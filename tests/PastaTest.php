<?php

declare(strict_types=1);

namespace Koriym\PastaLunch;

use PHPUnit\Framework\TestCase;

class PastaTest extends TestCase
{
    public function testAnalyze(): void
    {
        $pasta = new Pasta('https://example.com/docs/');
        $report = $pasta->analyze([__DIR__ . '/../src']);
        $this->assertInstanceOf(Report::class, $report);
    }

    public function testReportToText(): void
    {
        $pasta = new Pasta('https://example.com/docs/');
        $report = $pasta->analyze([__DIR__ . '/../src']);
        $text = $report->toText();
        $this->assertStringContainsString('Files:', $text);
    }

    public function testReportToMarkdown(): void
    {
        $pasta = new Pasta('https://example.com/docs/');
        $report = $pasta->analyze([__DIR__ . '/../src']);
        $md = $report->toMarkdown();
        $this->assertStringContainsString('## 🍝 Spaghetti Code Detection', $md);
        $this->assertStringContainsString('### Summary', $md);
    }

    public function testReportToHtml(): void
    {
        $pasta = new Pasta('https://example.com/docs/');
        $report = $pasta->analyze([__DIR__ . '/../src']);
        $html = $report->toHtml();
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Spaghetti Code Detection', $html);
    }

    public function testAnalyzeWithExcludePatterns(): void
    {
        $pasta = new Pasta('https://example.com/docs/');
        $report = $pasta->analyze([__DIR__ . '/../src'], ['Types.php']);
        $this->assertInstanceOf(Report::class, $report);
    }

    public function testAnalyzeWithEmptyExcludePatterns(): void
    {
        $pasta = new Pasta('https://example.com/docs/');
        $report = $pasta->analyze([__DIR__ . '/../src'], []);
        $this->assertInstanceOf(Report::class, $report);
    }
}
