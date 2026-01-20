<?php

declare(strict_types=1);

namespace Koriym\PastaLunch;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_filter;
use function basename;
use function count;
use function escapeshellarg;
use function explode;
use function fnmatch;
use function implode;
use function is_dir;
use function is_string;
use function preg_match;
use function preg_replace;
use function realpath;
use function shell_exec;
use function sprintf;
use function str_ends_with;
use function trim;
use function uasort;

/**
 * @psalm-import-type FileData from Types
 * @psalm-import-type GroupedFiles from Types
 * @psalm-import-type ReportData from Types
 * @psalm-import-type TargetDirs from Types
 * @psalm-import-type ExcludePatterns from Types
 * @psalm-import-type PhpFilePaths from Types
 */
final class Pasta
{
    public const THRESHOLDS = [
        'CouplingBetweenObjects' => [10, 15, 20],
        'CyclomaticComplexity' => [10, 15, 20],
        'NPathComplexity' => [100, 200, 500],
        'ExcessiveClassComplexity' => [50, 80, 120],
        'ExcessiveMethodLength' => [50, 100, 150],
        'ExcessiveParameterList' => [5, 10, 15],
        'TooManyFields' => [10, 15, 20],
        'TooManyPublicMethods' => [10, 15, 20],
    ];

    private const PHPMD_PATH = './vendor/bin/phpmd';

    public function __construct(
        private readonly string $docsBaseUrl,
    ) {
    }

    /**
     * @param TargetDirs      $targetDirs
     * @param ExcludePatterns $excludePatterns
     */
    public function analyze(array $targetDirs, array $excludePatterns = ['*Module.php']): Report
    {
        $targetDir = implode(',', $targetDirs);
        $cmd = sprintf('php -d error_reporting=E_ERROR %s %s text codesize,design 2>/dev/null', escapeshellarg(self::PHPMD_PATH), escapeshellarg($targetDir));
        $output = shell_exec($cmd);
        if (! is_string($output)) {
            $output = ''; // @codeCoverageIgnore
        }

        $files = $this->parsePhpmdOutput($output);
        if (! empty($excludePatterns)) {
            $files = array_filter($files, fn (array $file): bool => ! $this->matchesExcludePattern($file['path'], $excludePatterns));
        }

        $data = $this->buildReportData($files, $targetDirs, $excludePatterns);

        return new Report($data['totalFiles'], $data['grouped'], $this->docsBaseUrl);
    }

    /** @return array<string, FileData> */
    private function parsePhpmdOutput(string $output): array
    {
        /** @var array<string, FileData> $files */
        $files = [];
        foreach (explode("\n", trim($output)) as $line) {
            if (empty($line)) {
                continue; // @codeCoverageIgnore
            }

            if (preg_match('/^(.+?):(\d+)\s+(\w+)\s+(.+)$/', $line, $matches)) {
                $filePath = $matches[1];
                $lineNum = (int) $matches[2];
                $metric = $matches[3];
                $description = $matches[4];
                $value = 0;
                if (preg_match('/(\d+)/', $description, $numMatch)) {
                    $value = (int) $numMatch[1];
                }

                $relativePath = (string) preg_replace('#^.*/src/#', 'src/', $filePath);
                if (! isset($files[$relativePath])) {
                    $files[$relativePath] = ['path' => $relativePath, 'issues' => [], 'maxLevel' => 1];
                }

                $level = $this->getLevel($metric, $value);
                $files[$relativePath]['issues'][] = [
                    'metric' => $metric,
                    'value' => $value,
                    'line' => $lineNum,
                    'level' => $level,
                ];
                if ($level > $files[$relativePath]['maxLevel']) {
                    $files[$relativePath]['maxLevel'] = $level;
                }
            }
        }

        uasort($files, static fn (array $a, array $b): int => $b['maxLevel'] <=> $a['maxLevel']);

        return $files;
    }

    private function getLevel(string $metric, int $value): int
    {
        if (! isset(self::THRESHOLDS[$metric])) {
            return 2;
        }

        $thresholds = self::THRESHOLDS[$metric];
        if ($value <= $thresholds[0]) {
            return 1;
        }

        if ($value <= $thresholds[1]) {
            return 2;
        }

        if ($value <= $thresholds[2]) {
            return 3;
        }

        return 4;
    }

    /** @param ExcludePatterns $patterns */
    private function matchesExcludePattern(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, basename($path))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, FileData> $files
     * @param TargetDirs              $targetDirs
     * @param ExcludePatterns         $excludePatterns
     *
     * @return ReportData
     */
    private function buildReportData(array $files, array $targetDirs, array $excludePatterns): array
    {
        $allPhpFiles = $this->collectAllPhpFiles($targetDirs);
        $grouped = $this->groupFilesByLevel($files);
        $filesWithIssues = $this->getFilesWithIssues($files);
        $this->addCleanFiles($grouped, $allPhpFiles, $filesWithIssues, $excludePatterns);

        /** @var ReportData $result */
        $result = [
            'totalFiles' => count($allPhpFiles),
            'grouped' => $grouped,
        ];

        return $result;
    }

    /**
     * @param TargetDirs $targetDirs
     *
     * @return PhpFilePaths
     */
    private function collectAllPhpFiles(array $targetDirs): array
    {
        /** @var PhpFilePaths $allPhpFiles */
        $allPhpFiles = [];
        foreach ($targetDirs as $dir) {
            $dir = trim($dir);
            if (! is_dir($dir)) {
                continue; // @codeCoverageIgnore
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            );
            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $allPhpFiles[] = $file->getPathname();
                }
            }
        }

        return $allPhpFiles;
    }

    /**
     * @param array<string, FileData> $files
     *
     * @return GroupedFiles
     */
    private function groupFilesByLevel(array $files): array
    {
        /** @var GroupedFiles $grouped */
        $grouped = [4 => [], 3 => [], 2 => [], 1 => []];
        foreach ($files as $file) {
            $level = $file['maxLevel'];
            $grouped[$level][] = $file;
        }

        /** @var GroupedFiles $result */
        $result = $grouped;

        return $result;
    }

    /**
     * @param array<string, FileData> $files
     *
     * @return PhpFilePaths
     */
    private function getFilesWithIssues(array $files): array
    {
        /** @var PhpFilePaths $filesWithIssues */
        $filesWithIssues = [];
        foreach ($files as $file) {
            $realPath = realpath($file['path']);
            $filesWithIssues[] = $realPath !== false ? $realPath : $file['path'];
        }

        return $filesWithIssues;
    }

    /**
     * @param GroupedFiles    $grouped
     * @param PhpFilePaths    $allPhpFiles
     * @param PhpFilePaths    $filesWithIssues
     * @param ExcludePatterns $excludePatterns
     */
    private function addCleanFiles(array &$grouped, array $allPhpFiles, array $filesWithIssues, array $excludePatterns): void
    {
        foreach ($allPhpFiles as $phpFile) {
            if ($this->isFileAlreadyIncluded($phpFile, $filesWithIssues)) {
                continue;
            }

            if ($this->matchesExcludePattern($phpFile, $excludePatterns)) {
                continue;
            }

            $grouped[1][] = ['path' => $phpFile, 'maxLevel' => 1, 'issues' => []];
        }
    }

    /** @param PhpFilePaths $filesWithIssues */
    private function isFileAlreadyIncluded(string $phpFile, array $filesWithIssues): bool
    {
        $realPath = realpath($phpFile);
        foreach ($filesWithIssues as $issueFile) {
            if ($realPath === $issueFile || str_ends_with($issueFile, basename($phpFile))) {
                return true;
            }
        }

        return false;
    }
}
