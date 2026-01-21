<?php

declare(strict_types=1);

namespace Koriym\PastaLunch;

use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    private function createReportWithIssues(): Report
    {
        $grouped = [
            4 => [
                [
                    'path' => 'src/MammaMia.php',
                    'maxLevel' => 4,
                    'issues' => [
                        ['metric' => 'CyclomaticComplexity', 'value' => 25, 'line' => 10, 'level' => 4],
                        ['metric' => 'NPathComplexity', 'value' => 600, 'line' => 10, 'level' => 4],
                    ],
                ],
            ],
            3 => [
                [
                    'path' => 'src/Grande.php',
                    'maxLevel' => 3,
                    'issues' => [
                        ['metric' => 'ExcessiveMethodLength', 'value' => 120, 'line' => 20, 'level' => 3],
                    ],
                ],
            ],
            2 => [
                [
                    'path' => 'src/Medio.php',
                    'maxLevel' => 2,
                    'issues' => [
                        ['metric' => 'TooManyFields', 'value' => 12, 'line' => 0, 'level' => 2],
                        ['metric' => 'UnknownMetric', 'value' => 5, 'line' => 5, 'level' => 2],
                    ],
                ],
            ],
            1 => [
                ['path' => 'src/Clean.php', 'maxLevel' => 1, 'issues' => []],
            ],
        ];

        return new Report(4, $grouped, 'https://example.com/docs/');
    }

    private function createEmptyReport(): Report
    {
        $grouped = [4 => [], 3 => [], 2 => [], 1 => []];

        return new Report(0, $grouped, 'https://example.com/docs/');
    }

    public function testCounts(): void
    {
        $report = $this->createReportWithIssues();
        $this->assertSame(1, $report->counts[4]);
        $this->assertSame(1, $report->counts[3]);
        $this->assertSame(1, $report->counts[2]);
        $this->assertSame(1, $report->counts[1]);
    }

    public function testToTextWithIssues(): void
    {
        $report = $this->createReportWithIssues();
        $text = $report->toText();
        $this->assertStringContainsString('Mamma Mia!', $text);
        $this->assertStringContainsString('Grande', $text);
        $this->assertStringContainsString('CyclomaticComplexity', $text);
        $this->assertStringContainsString('L10', $text);
        $this->assertStringContainsString('See: https://example.com/docs/', $text);
    }

    public function testToTextWithLowLevelIssue(): void
    {
        $grouped = [
            4 => [],
            3 => [],
            2 => [
                [
                    'path' => 'src/Test.php',
                    'maxLevel' => 2,
                    'issues' => [
                        ['metric' => 'CyclomaticComplexity', 'value' => 12, 'line' => 5, 'level' => 1],
                    ],
                ],
            ],
            1 => [],
        ];
        $report = new Report(1, $grouped, 'https://example.com/docs/');
        $text = $report->toText();
        $this->assertStringContainsString('Files:', $text);
    }

    public function testToMarkdownWithIssues(): void
    {
        $report = $this->createReportWithIssues();
        $md = $report->toMarkdown();
        $this->assertStringContainsString('## 🍝 Spaghetti Code Detection', $md);
        $this->assertStringContainsString('### 🍝🍝🍝🍝 Mamma Mia!', $md);
        $this->assertStringContainsString('### 🍝🍝🍝 Grande', $md);
        $this->assertStringContainsString('## Details', $md);
        $this->assertStringContainsString('[CyclomaticComplexity]', $md);
        $this->assertStringContainsString(':L10', $md);
        $this->assertStringContainsString('(Remaining files)', $md);
    }

    public function testToMarkdownWithNoLineInfo(): void
    {
        $report = $this->createReportWithIssues();
        $md = $report->toMarkdown();
        $this->assertStringContainsString('TooManyFields', $md);
    }

    public function testToHtmlWithIssues(): void
    {
        $report = $this->createReportWithIssues();
        $html = $report->toHtml();
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Spaghetti Code Detection', $html);
        $this->assertStringContainsString('view-files', $html);
        $this->assertStringContainsString('view-issues', $html);
        $this->assertStringContainsString('stat-card level-4', $html);
        $this->assertStringContainsString('stat-card level-3', $html);
        $this->assertStringContainsString('stat-card level-2', $html);
        $this->assertStringContainsString('stat-card level-1', $html);
        $this->assertStringContainsString('detail-table', $html);
        $this->assertStringContainsString('issue-type-section', $html);
        $this->assertStringContainsString(':10', $html);
    }

    public function testToHtmlWithEmptyReport(): void
    {
        $report = $this->createEmptyReport();
        $html = $report->toHtml();
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('files-count">0</div>', $html);
        $this->assertStringContainsString('files analyzed', $html);
    }

    public function testToHtmlZeroTotalFiles(): void
    {
        $grouped = [4 => [], 3 => [], 2 => [], 1 => []];
        $report = new Report(0, $grouped, 'https://example.com/docs/');
        $html = $report->toHtml();
        $this->assertStringContainsString('0.0%', $html);
    }

    public function testUnknownMetricInThresholds(): void
    {
        $grouped = [
            4 => [],
            3 => [],
            2 => [
                [
                    'path' => 'src/Test.php',
                    'maxLevel' => 2,
                    'issues' => [
                        ['metric' => 'UnknownMetric', 'value' => 100, 'line' => 0, 'level' => 2],
                    ],
                ],
            ],
            1 => [],
        ];
        $report = new Report(1, $grouped, 'https://example.com/docs/');
        $text = $report->toText();
        $this->assertStringContainsString('?', $text);
        $md = $report->toMarkdown();
        $this->assertStringContainsString('UnknownMetric', $md);
        $html = $report->toHtml();
        $this->assertStringContainsString('UnknownMetric', $html);
    }

    public function testMetricLinkWithoutSlug(): void
    {
        $grouped = [
            4 => [],
            3 => [],
            2 => [
                [
                    'path' => 'src/Test.php',
                    'maxLevel' => 2,
                    'issues' => [
                        ['metric' => 'NoDocsMetric', 'value' => 100, 'line' => 5, 'level' => 2],
                    ],
                ],
            ],
            1 => [],
        ];
        $report = new Report(1, $grouped, 'https://example.com/docs/');
        $md = $report->toMarkdown();
        $this->assertStringContainsString('NoDocsMetric:', $md);
    }
}
