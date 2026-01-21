<?php

declare(strict_types=1);

namespace Koriym\PastaLunch;

/**
 * PASTA Lunch Domain Types for Psalm
 *
 * This file contains Psalm type definitions for the PASTA Lunch spaghetti code detector.
 * These types enhance static analysis and provide better IDE support.
 *
 * Issue Types
 *
 * @psalm-type Issue = array{metric: string, value: int, line: int, level: int}
 * @psalm-type IssueList = list<Issue>
 * @psalm-type FileData = array{path: string, maxLevel: int, issues: IssueList}
 * @psalm-type FileDataList = list<FileData>
 * @psalm-type GroupedFiles = array{4: FileDataList, 3: FileDataList, 2: FileDataList, 1: FileDataList}
 * @psalm-type ReportData = array{totalFiles: int, grouped: GroupedFiles}
 * @psalm-type FileCounts = array{4: int, 3: int, 2: int, 1: int}
 * @psalm-type IssueByTypeItem = array{path: string, fullPath: string, value: int, line: int, level: int}
 * @psalm-type IssuesByType = array<string, list<IssueByTypeItem>>
 * @psalm-type TargetDirs = list<string>
 * @psalm-type ExcludePatterns = list<string>
 * @psalm-type PhpFilePaths = list<string>
 */
final class Types
{
}
