<?php

declare(strict_types=1);

namespace Koriym\PastaLunch;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

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

    public function testGetLevelViaReflection(): void
    {
        $pasta = new Pasta('https://example.com/docs/');
        $reflection = new ReflectionClass($pasta);
        $method = $reflection->getMethod('getLevel');

        // Level 1: value <= threshold[0]
        $this->assertSame(1, $method->invoke($pasta, 'CyclomaticComplexity', 10));

        // Level 2: value <= threshold[1]
        $this->assertSame(2, $method->invoke($pasta, 'CyclomaticComplexity', 15));

        // Level 3: value <= threshold[2]
        $this->assertSame(3, $method->invoke($pasta, 'CyclomaticComplexity', 20));

        // Level 4: value > threshold[2]
        $this->assertSame(4, $method->invoke($pasta, 'CyclomaticComplexity', 25));

        // Unknown metric returns level 2
        $this->assertSame(2, $method->invoke($pasta, 'UnknownMetric', 100));
    }
}
