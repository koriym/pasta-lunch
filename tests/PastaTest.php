<?php

declare(strict_types=1);

namespace Koriym\PastaLunch;

use PHPUnit\Framework\TestCase;

class PastaTest extends TestCase
{
    public function testAnalyze(): void
    {
        $pasta = new Pasta('https://example.com/docs');
        $report = $pasta->analyze([__DIR__ . '/../src']);
        $this->assertInstanceOf(Report::class, $report);
    }

    public function testReportToText(): void
    {
        $pasta = new Pasta('https://example.com/docs');
        $report = $pasta->analyze([__DIR__ . '/../src']);
        $this->assertNotNull($report);
        $text = $report->toText();
        $this->assertStringContainsString('Files:', $text);
    }
}
