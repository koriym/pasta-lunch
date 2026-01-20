<?php

declare(strict_types=1);

namespace Koriym\PastaLunch;

use function array_map;
use function array_merge;
use function assert;
use function count;
use function date;
use function file_get_contents;
use function htmlspecialchars;
use function implode;
use function number_format;
use function preg_replace;
use function stream_isatty;
use function uasort;
use function usort;

use const STDOUT;

/**
 * @psalm-import-type GroupedFiles from Types
 * @psalm-import-type FileCounts from Types
 * @psalm-import-type IssuesByType from Types
 */
final class Report
{
    private const LEVELS = [
        1 => ['emoji' => "\u{1F35D}", 'name' => 'Piccolo'],
        2 => ['emoji' => "\u{1F35D}\u{1F35D}", 'name' => 'Medio'],
        3 => ['emoji' => "\u{1F35D}\u{1F35D}\u{1F35D}", 'name' => 'Grande'],
        4 => ['emoji' => "\u{1F35D}\u{1F35D}\u{1F35D}\u{1F35D}", 'name' => 'Mamma Mia!'],
    ];

    private const LEVEL_BADGES = [
        4 => "\u{26A0}\u{FE0F}\u{26A0}\u{FE0F} Unmaintainable",
        3 => "\u{26A0}\u{FE0F} Refactoring required",
        2 => 'Acceptable',
        1 => 'Clean code',
    ];

    private const METRIC_DOCS = [
        'CouplingBetweenObjects' => 'coupling-between-objects',
        'CyclomaticComplexity' => 'cyclomatic-complexity',
        'NPathComplexity' => 'npath-complexity',
        'ExcessiveClassComplexity' => 'excessive-class-complexity',
        'ExcessiveMethodLength' => 'excessive-method-length',
        'ExcessiveParameterList' => 'excessive-parameter-list',
        'TooManyFields' => 'too-many-fields',
        'TooManyPublicMethods' => 'too-many-public-methods',
        'DevelopmentCodeFragment' => 'development-code-fragment',
    ];

    private const COLORS = [
        'bold_red' => "\033[1;31m",
        'red' => "\033[31m",
        'blue' => "\033[34m",
        'green' => "\033[32m",
        'reset' => "\033[0m",
        'bold' => "\033[1m",
        'dim' => "\033[2m",
    ];

    /** @var FileCounts */
    public readonly array $counts;

    /** @param GroupedFiles $grouped */
    public function __construct(
        public readonly int $totalFiles,
        public readonly array $grouped,
        private readonly string $docsBaseUrl,
    ) {
        $this->counts = [
            4 => count($grouped[4]),
            3 => count($grouped[3]),
            2 => count($grouped[2]),
            1 => count($grouped[1]),
        ];
    }

    public function toText(): string
    {
        $useColor = stream_isatty(STDOUT);
        $color = static fn (string $c, string $s): string => $useColor ? self::COLORS[$c] . $s . self::COLORS['reset'] : $s;
        $levelColor = fn (int $l, string $s): string => $useColor ? $this->getLevelColor($l) . $s . self::COLORS['reset'] : $s;

        $output = "\n";

        foreach ([4, 3, 2] as $levelNum) {
            $levelFiles = $this->grouped[$levelNum];
            if ($levelFiles === []) {
                continue;
            }

            $lvl = self::LEVELS[$levelNum];
            foreach ($levelFiles as $file) {
                $shortPath = preg_replace('#^(.*/)?src/#', 'src/', $file['path']);
                $output .= $levelColor($levelNum, "{$lvl['emoji']} {$lvl['name']}") . " - {$shortPath}\n";

                foreach ($file['issues'] as $issue) {
                    if ($issue['level'] >= 2) {
                        $thresholds = $this->getThresholdsString($issue['metric']);
                        $lineInfo = $issue['line'] > 0 ? " L{$issue['line']}" : '';
                        $output .= "  {$issue['metric']}: {$issue['value']}({$thresholds}){$lineInfo}\n";
                        $slug = self::METRIC_DOCS[$issue['metric']] ?? null;
                        if ($slug) {
                            $output .= $color('dim', '  See: ' . $this->docsBaseUrl . $slug) . "\n";
                        }
                    }
                }

                $output .= "\n";
            }
        }

        $output .= $color('bold', "Files: {$this->totalFiles}") . ' | ';
        $output .= $levelColor(1, "\u{1F35D} {$this->counts[1]}") . ' | ';
        $output .= $levelColor(2, "\u{1F35D}\u{1F35D} {$this->counts[2]}") . ' | ';
        $output .= $levelColor(3, "\u{1F35D}\u{1F35D}\u{1F35D} {$this->counts[3]}") . ' | ';
        $output .= $levelColor(4, "\u{1F35D}\u{1F35D}\u{1F35D}\u{1F35D} {$this->counts[4]}") . "\n\n";

        return $output;
    }

    public function toMarkdown(): string
    {
        $output = "## \u{1F35D} Spaghetti Code Detection ({$this->totalFiles} files)\n\n";

        foreach ([4, 3, 2, 1] as $levelNum) {
            $lvl = self::LEVELS[$levelNum];
            $count = count($this->grouped[$levelNum]);
            if ($count === 0) {
                continue;
            }

            $output .= "### {$lvl['emoji']} {$lvl['name']} ({$count})\n\n";
            if ($levelNum === 1) {
                $output .= "(Remaining files)\n\n";
                continue;
            }

            $fileNames = array_map(static fn ($f) => preg_replace('#^.*/src/(Resource/)?#', '', $f['path']), $this->grouped[$levelNum]);
            $output .= implode(', ', $fileNames) . "\n\n";
        }

        $detailFiles = array_merge($this->grouped[4], $this->grouped[3], $this->grouped[2]);
        if (! empty($detailFiles)) {
            $output .= "---\n\n## Details\n\n";
            foreach ($detailFiles as $file) {
                $shortPath = preg_replace('#^src/(Resource/)?#', '', $file['path']);
                $lvl = self::LEVELS[$file['maxLevel']];
                $output .= "### {$shortPath} {$lvl['emoji']} {$lvl['name']}\n\n**Issues:**\n";
                foreach ($file['issues'] as $issue) {
                    if ($issue['level'] >= 2) {
                        $threshold = Pasta::THRESHOLDS[$issue['metric']][0] ?? '?';
                        $lineInfo = $issue['line'] > 0 ? ":L{$issue['line']}" : '';
                        $metricLink = $this->getMetricLink($issue['metric'], 'md');
                        $output .= "- {$metricLink}: {$issue['value']}({$threshold}){$lineInfo}\n";
                    }
                }

                $output .= "\n";
            }
        }

        $output .= "---\n\n### Summary\n\n";
        $output .= "| Level | | Files |\n|-------|---|-------|\n";
        $output .= "| \u{1F35D}\u{1F35D}\u{1F35D}\u{1F35D} Mamma Mia! | \u{26A0}\u{FE0F}\u{26A0}\u{FE0F} Unmaintainable | {$this->counts[4]} |\n";
        $output .= "| \u{1F35D}\u{1F35D}\u{1F35D} Grande | \u{26A0}\u{FE0F} Refactoring required | {$this->counts[3]} |\n";
        $output .= "| \u{1F35D}\u{1F35D} Medio | Acceptable | {$this->counts[2]} |\n";
        $output .= "| \u{1F35D} Piccolo | Clean code | {$this->counts[1]} |\n";

        return $output;
    }

    public function toHtml(): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $css = $this->getHtmlCss();
        $js = $this->getHtmlJs();

        $output = $this->buildHtmlHead($css, $timestamp);
        $output .= $this->buildHtmlSummaryTable();
        $output .= $this->buildHtmlLevelSections();
        $output .= $this->buildHtmlDetails();
        $output .= "<script>{$js}</script>\n</body></html>\n";

        return $output;
    }

    private function getHtmlCss(): string
    {
        $css = file_get_contents(__DIR__ . '/../assets/report.css');
        assert($css !== false);

        return $css;
    }

    private function getHtmlJs(): string
    {
        $js = file_get_contents(__DIR__ . '/../assets/report.js');
        assert($js !== false);

        return $js;
    }

    private function buildHtmlHead(string $css, string $timestamp): string
    {
        $output = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
        $output .= "<meta charset=\"UTF-8\">\n<meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">\n";
        $output .= "<title>\u{1F35D} Spaghetti Code Detection</title>\n<style>{$css}</style>\n</head>\n<body>\n";
        $output .= "<h1>\u{1F35D} Spaghetti Code Detection</h1>\n";
        $output .= "<p>{$this->totalFiles} files analyzed</p>\n";
        $output .= "<p class=\"meta\">Generated: {$timestamp}</p>\n";

        return $output;
    }

    private function buildHtmlSummaryTable(): string
    {
        $output = "<table><thead><tr><th>Level</th><th>Status</th><th>Files</th></tr></thead><tbody>\n";
        foreach ([4, 3, 2, 1] as $n) {
            $l = self::LEVELS[$n];
            /** @psalm-suppress InvalidOperand */
            $pct = $this->totalFiles > 0 ? number_format($this->counts[$n] / $this->totalFiles * 100, 1) : '0.0';
            $output .= "<tr><td><span class=\"pasta\">{$l['emoji']}</span> {$l['name']}</td><td><span class=\"status-{$n}\">" . self::LEVEL_BADGES[$n] . "</span></td><td>{$this->counts[$n]} ({$pct}%)</td></tr>\n";
        }

        $output .= "</tbody></table>\n<h2>Files by Level</h2>\n";

        return $output;
    }

    private function buildHtmlLevelSections(): string
    {
        $output = '';
        foreach ([4, 3, 2, 1] as $levelNum) {
            $lvl = self::LEVELS[$levelNum];
            $count = count($this->grouped[$levelNum]);
            if ($count === 0) {
                continue;
            }

            $output .= "<div class=\"level-section level-{$levelNum}\"><h3><span class=\"pasta\">{$lvl['emoji']}</span> {$lvl['name']} ({$count})</h3>\n";
            if ($levelNum === 1) {
                $output .= "<p class=\"remaining\">(Remaining files)</p></div>\n";
                continue;
            }

            $fileLinks = array_map(static function (array $f): string {
                $shortPath = (string) preg_replace('#^(.*/)?src/(Resource/)?#', '', $f['path']);
                $id = (string) preg_replace('/[^a-zA-Z0-9]/', '-', $shortPath);

                return '<a href="#file-' . $id . '">' . htmlspecialchars($shortPath) . '</a>';
            }, $this->grouped[$levelNum]);
            $output .= '<div class="file-list">' . implode(', ', $fileLinks) . "</div></div>\n";
        }

        return $output;
    }

    private function buildHtmlDetails(): string
    {
        $detailFiles = array_merge($this->grouped[4], $this->grouped[3], $this->grouped[2]);
        if (empty($detailFiles)) {
            return '';
        }

        $issuesByType = $this->collectIssuesByType($detailFiles);
        uasort($issuesByType, static fn (array $a, array $b): int => count($b) <=> count($a));

        $output = "<h2>Details</h2>\n";
        $output .= "<div class=\"view-toggle\">\n";
        $output .= "<button class=\"view-btn active\" data-view=\"files\" onclick=\"switchView('files')\">by Files</button>\n";
        $output .= "<button class=\"view-btn\" data-view=\"issues\" onclick=\"switchView('issues')\">by Issues</button>\n";
        $output .= "</div>\n";

        $output .= $this->buildHtmlFileCards($detailFiles);
        $output .= $this->buildHtmlIssueCards($issuesByType);

        return $output;
    }

    /**
     * @param list<array{path: string, maxLevel: int, issues: list<array{metric: string, value: int, line: int, level: int}>}> $detailFiles
     *
     * @return IssuesByType
     */
    private function collectIssuesByType(array $detailFiles): array
    {
        /** @var IssuesByType $issuesByType */
        $issuesByType = [];
        foreach ($detailFiles as $file) {
            $shortPath = (string) preg_replace('#^(.*/)?src/(Resource/)?#', '', $file['path']);
            foreach ($file['issues'] as $issue) {
                if ($issue['level'] < 2) {
                    continue;
                }

                $metric = $issue['metric'];
                if (! isset($issuesByType[$metric])) {
                    $issuesByType[$metric] = [];
                }

                $issuesByType[$metric][] = [
                    'path' => $shortPath,
                    'value' => $issue['value'],
                    'line' => $issue['line'],
                    'level' => $issue['level'],
                ];
            }
        }

        return $issuesByType;
    }

    /** @param list<array{path: string, maxLevel: int, issues: list<array{metric: string, value: int, line: int, level: int}>}> $detailFiles */
    private function buildHtmlFileCards(array $detailFiles): string
    {
        $output = "<div id=\"view-files\" class=\"view-section active\">\n";
        foreach ($detailFiles as $file) {
            $shortPath = (string) preg_replace('#^(.*/)?src/(Resource/)?#', '', $file['path']);
            $id = (string) preg_replace('/[^a-zA-Z0-9]/', '-', $shortPath);
            $lvl = self::LEVELS[$file['maxLevel']];
            $levelNum = $file['maxLevel'];
            $output .= "<div class=\"detail-card\" id=\"file-{$id}\"><div class=\"detail-header\">";
            $output .= '<span class="detail-path">' . htmlspecialchars($shortPath) . '</span>';
            $output .= "<span class=\"badge badge-{$levelNum}\">{$lvl['emoji']} {$lvl['name']}</span></div>\n";
            $output .= "<ul class=\"issue-list\">\n";
            foreach ($file['issues'] as $issue) {
                if ($issue['level'] < 2) {
                    continue;
                }

                $threshold = Pasta::THRESHOLDS[$issue['metric']][0] ?? '?';
                $lineInfo = $issue['line'] > 0 ? "<span class=\"metric-line\">:L{$issue['line']}</span>" : '';
                $metricLink = $this->getMetricLink($issue['metric'], 'html');
                $output .= "<li class=\"issue-item\"><span class=\"metric-name\">{$metricLink}</span>: ";
                $output .= "<span class=\"metric-value\">{$issue['value']}</span>";
                $output .= "<span class=\"metric-threshold\">({$threshold})</span>{$lineInfo}</li>\n";
            }

            $output .= "</ul></div>\n";
        }

        $output .= "</div>\n";

        return $output;
    }

    /** @param IssuesByType $issuesByType */
    private function buildHtmlIssueCards(array $issuesByType): string
    {
        $output = "<div id=\"view-issues\" class=\"view-section\">\n";
        foreach ($issuesByType as $metric => $files) {
            $metricLink = $this->getMetricLink($metric, 'html');
            $threshold = Pasta::THRESHOLDS[$metric][0] ?? '?';
            $fileCount = count($files);
            usort($files, static fn (array $a, array $b): int => $b['value'] <=> $a['value']);

            $output .= "<div class=\"issue-type-card\">\n";
            $output .= "<div class=\"issue-type-header\">\n";
            $output .= "<span class=\"issue-type-name\">{$metricLink} <span class=\"metric-threshold\">(threshold: {$threshold})</span></span>\n";
            $output .= "<span class=\"issue-count\">{$fileCount} files</span>\n";
            $output .= "</div>\n";
            foreach ($files as $f) {
                $lineInfo = $f['line'] > 0 ? ":L{$f['line']}" : '';
                $output .= "<div class=\"file-item\">\n";
                $output .= '<span class="file-item-path">' . htmlspecialchars($f['path']) . "{$lineInfo}</span>\n";
                $output .= "<span class=\"file-item-value\">{$f['value']}</span>\n";
                $output .= "</div>\n";
            }

            $output .= "</div>\n";
        }

        $output .= "</div>\n";

        return $output;
    }

    private function getThresholdsString(string $metric): string
    {
        if (! isset(Pasta::THRESHOLDS[$metric])) {
            return '?';
        }

        $t = Pasta::THRESHOLDS[$metric];

        return "{$t[0]}:{$t[1]}:{$t[2]}";
    }

    /** @codeCoverageIgnore */
    private function getLevelColor(int $level): string
    {
        return match ($level) {
            4 => self::COLORS['bold_red'],
            3 => self::COLORS['red'],
            2 => self::COLORS['blue'],
            default => self::COLORS['green'],
        };
    }

    private function getMetricLink(string $metric, string $format): string
    {
        $slug = self::METRIC_DOCS[$metric] ?? null;
        if ($slug === null) {
            return $metric;
        }

        $url = $this->docsBaseUrl . $slug;
        if ($format === 'html') {
            return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener noreferrer\">{$metric}</a>";
        }

        return "[{$metric}]({$url})";
    }
}
