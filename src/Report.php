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

use const ENT_QUOTES;
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
        4 => 'Unmaintainable',
        3 => 'Refactoring required',
        2 => 'Acceptable',
        1 => 'Clean code',
    ];

    private const COPY_ICON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/></svg>';

    private const METRIC_DOCS = [
        'CouplingBetweenObjects' => 'coupling-between-objects',
        'CyclomaticComplexity' => 'cyclomatic-complexity',
        'NPathComplexity' => 'npath-complexity',
        'ExcessiveClassComplexity' => 'excessive-class-complexity',
        'ExcessiveMethodLength' => 'excessive-method-length',
        'ExcessiveParameterList' => 'excessive-parameter-list',
        'TooManyFields' => 'too-many-fields',
        'TooManyPublicMethods' => 'too-many-public-methods',
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
        $output .= $this->buildHtmlStatsRow();
        $output .= $this->buildHtmlMainGrid();
        $output .= $this->buildHtmlFooter();
        $output .= "<div class=\"toast\" id=\"toast\"></div>\n";
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
        $output .= "<title>\u{1F35D} Spaghetti Code Detection</title>\n";
        $output .= "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
        $output .= "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
        $output .= "<link href=\"https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap\" rel=\"stylesheet\">\n";
        $output .= "<style>{$css}</style>\n</head>\n<body>\n";
        $output .= "<div class=\"container\">\n";
        $output .= "<header class=\"header\">\n";
        $output .= "<div class=\"header-left\">\n";
        $output .= "<h1>\u{1F35D} PASTA</h1>\n";
        $output .= "<p class=\"subtitle\">PHP Alert for Spaghetti Twisted Architecture</p>\n";
        $output .= "<p class=\"meta\">Generated: {$timestamp}</p>\n";
        $output .= "</div>\n";
        $output .= "<div class=\"header-right\">\n";
        $output .= "<div class=\"files-count\">{$this->totalFiles}</div>\n";
        $output .= "<div class=\"files-label\">files analyzed</div>\n";
        $output .= "</div>\n";
        $output .= "</header>\n";

        return $output;
    }

    private function buildHtmlStatsRow(): string
    {
        $output = "<section class=\"stats-row\">\n";
        foreach ([4, 3, 2, 1] as $n) {
            $l = self::LEVELS[$n];
            /** @psalm-suppress InvalidOperand */
            $pct = $this->totalFiles > 0 ? number_format($this->counts[$n] / $this->totalFiles * 100, 1) : '0.0';
            $output .= "<div class=\"stat-card level-{$n}\">\n";
            $output .= "<div class=\"label\"><span class=\"pasta\">{$l['emoji']}</span> {$l['name']}</div>\n";
            $output .= "<div class=\"value\">{$this->counts[$n]}</div>\n";
            $output .= "<div class=\"detail\">{$pct}% &middot; " . self::LEVEL_BADGES[$n] . "</div>\n";
            $output .= "</div>\n";
        }

        $output .= "</section>\n";

        return $output;
    }

    private function buildHtmlMainGrid(): string
    {
        $output = "<div class=\"main-grid\">\n";
        $output .= $this->buildHtmlSidebar();
        $output .= $this->buildHtmlContent();
        $output .= "</div>\n";
        $output .= "</div>\n"; // close container

        return $output;
    }

    private function buildHtmlSidebar(): string
    {
        $output = "<aside class=\"sidebar\">\n";
        foreach ([4, 3, 2] as $levelNum) {
            $count = count($this->grouped[$levelNum]);
            if ($count === 0) {
                continue;
            }

            $lvl = self::LEVELS[$levelNum];
            $output .= "<div class=\"sidebar-card\">\n";
            $output .= "<div class=\"sidebar-card-header level-{$levelNum}\">\n";
            $output .= "<span class=\"indicator\"></span>\n";
            $output .= "{$lvl['name']}\n";
            $output .= "<span class=\"count\">{$count}</span>\n";
            $output .= "</div>\n";
            $output .= "<div class=\"sidebar-file-list\">\n";
            foreach ($this->grouped[$levelNum] as $file) {
                $shortPath = (string) preg_replace('#^(.*/)?src/(Resource/)?#', '', $file['path']);
                $id = (string) preg_replace('/[^a-zA-Z0-9]/', '-', $shortPath);
                $output .= '<a href="#file-' . $id . '" class="sidebar-file-item">' . htmlspecialchars($shortPath, ENT_QUOTES, 'UTF-8') . "</a>\n";
            }

            $output .= "</div>\n";
            $output .= "</div>\n";
        }

        $output .= "</aside>\n";

        return $output;
    }

    private function buildHtmlContent(): string
    {
        $output = "<main class=\"content\">\n";
        $output .= "<div class=\"content-header\">\n";
        $output .= "<h2 class=\"content-title\">Details</h2>\n";
        $output .= "<div class=\"view-toggle\">\n";
        $output .= "<button class=\"view-btn active\" data-view=\"files\" onclick=\"switchView('files')\">by Files</button>\n";
        $output .= "<button class=\"view-btn\" data-view=\"issues\" onclick=\"switchView('issues')\">by Issues</button>\n";
        $output .= "</div>\n";
        $output .= "</div>\n";

        $detailFiles = array_merge($this->grouped[4], $this->grouped[3], $this->grouped[2]);
        $issuesByType = $this->collectIssuesByType($detailFiles);
        uasort($issuesByType, static fn (array $a, array $b): int => count($b) <=> count($a));

        $output .= $this->buildHtmlFileTable($detailFiles);
        $output .= $this->buildHtmlIssueCards($issuesByType);
        $output .= "</main>\n";

        return $output;
    }

    private function buildHtmlFooter(): string
    {
        return "<footer class=\"footer\">\n<a href=\"https://koriym.github.io/pasta-lunch/\" target=\"_blank\" rel=\"noopener noreferrer\">\u{1F35D} PASTA Lunch</a>\n</footer>\n";
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
                    'fullPath' => $file['path'],
                    'value' => $issue['value'],
                    'line' => $issue['line'],
                    'level' => $issue['level'],
                ];
            }
        }

        return $issuesByType;
    }

    /** @param list<array{path: string, maxLevel: int, issues: list<array{metric: string, value: int, line: int, level: int}>}> $detailFiles */
    private function buildHtmlFileTable(array $detailFiles): string
    {
        $output = "<div id=\"view-files\" class=\"view-section active\">\n";
        $output .= "<div class=\"detail-table\">\n<table>\n<thead>\n<tr>\n";
        $output .= "<th style=\"width: 100px;\">Level</th>\n<th>File</th>\n<th>Issues</th>\n";
        $output .= "</tr>\n</thead>\n<tbody>\n";

        foreach ($detailFiles as $file) {
            $shortPath = (string) preg_replace('#^(.*/)?src/(Resource/)?#', '', $file['path']);
            $id = (string) preg_replace('/[^a-zA-Z0-9]/', '-', $shortPath);
            $lvl = self::LEVELS[$file['maxLevel']];
            $levelNum = $file['maxLevel'];

            $output .= "<tr id=\"file-{$id}\">\n";
            $output .= "<td class=\"level-cell\">\n";
            $output .= "<span class=\"level-badge level-{$levelNum}\">{$lvl['name']}<span class=\"level-pasta\">{$lvl['emoji']}</span></span>\n";
            $output .= "</td>\n";
            $output .= "<td class=\"file-cell\">\n";
            $output .= '<span class="path">' . htmlspecialchars($shortPath, ENT_QUOTES, 'UTF-8') . "</span>\n";
            $output .= "</td>\n";
            $output .= "<td class=\"issues-cell\">\n";

            foreach ($file['issues'] as $issue) {
                if ($issue['level'] < 2) {
                    continue;
                }

                $thresholds = $this->getThresholdsString($issue['metric']);
                $lineInfo = $issue['line'] > 0 ? ":{$issue['line']}" : '';
                $copyPath = htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8') . $lineInfo;
                $metricLink = $this->getMetricLink($issue['metric'], 'html');
                $displayValue = (string) $issue['value'];
                $output .= "<div class=\"issue-row\">\n";
                $output .= "<span class=\"issue-name\">{$metricLink}</span>\n";
                $output .= "<span class=\"issue-value\">{$displayValue}</span>\n";
                $output .= "<span class=\"issue-threshold\">({$thresholds})</span>\n";
                $output .= "<span class=\"issue-copy\" onclick=\"copyPath(this, '{$copyPath}')\">" . self::COPY_ICON_SVG . "</span>\n";
                $output .= "</div>\n";
            }

            $output .= "</td>\n</tr>\n";
        }

        $output .= "</tbody>\n</table>\n</div>\n</div>\n";

        return $output;
    }

    /** @param IssuesByType $issuesByType */
    private function buildHtmlIssueCards(array $issuesByType): string
    {
        $output = "<div id=\"view-issues\" class=\"view-section\">\n";
        foreach ($issuesByType as $metric => $files) {
            $metricLink = $this->getMetricLink($metric, 'html');
            $thresholds = $this->getThresholdsString($metric);
            $fileCount = count($files);
            usort($files, static fn (array $a, array $b): int => $b['value'] <=> $a['value']);

            $output .= "<div class=\"issue-type-section\">\n";
            $output .= "<div class=\"issue-type-header\">\n";
            $output .= "<span class=\"issue-type-name\">{$metricLink}</span>\n";
            $output .= "<span class=\"issue-type-meta\">threshold: {$thresholds} &middot; {$fileCount} files</span>\n";
            $output .= "</div>\n";
            $output .= "<div class=\"issue-type-content\">\n";
            foreach ($files as $f) {
                $lineInfo = $f['line'] > 0 ? ":L{$f['line']}" : '';
                $copyPath = htmlspecialchars($f['fullPath'], ENT_QUOTES, 'UTF-8') . ($f['line'] > 0 ? ":{$f['line']}" : '');
                $lvl = self::LEVELS[$f['level']];
                $displayValue = (string) $f['value'];
                $output .= "<div class=\"issue-file-row\">\n";
                $output .= '<span class="issue-file-path">' . htmlspecialchars($f['path'], ENT_QUOTES, 'UTF-8') . "{$lineInfo}</span>\n";
                $output .= "<span class=\"issue-file-right\">\n";
                $output .= "<span class=\"issue-file-value\">{$displayValue}</span>\n";
                $output .= "<span class=\"level-badge-mini level-{$f['level']}\">{$lvl['emoji']}</span>\n";
                $output .= "<span class=\"issue-file-copy\" onclick=\"copyPath(this, '{$copyPath}')\">" . self::COPY_ICON_SVG . "</span>\n";
                $output .= "</span>\n";
                $output .= "</div>\n";
            }

            $output .= "</div>\n</div>\n";
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
