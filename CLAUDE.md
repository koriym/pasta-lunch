# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Pasta Lunch is a spaghetti code detector that wraps PHPMD metrics to identify complex, tangled code. It outputs reports in markdown or HTML format with Italian-themed severity levels (Piccolo → Medio → Grande → Mamma Mia!).

## Commands

```bash
# Run the detector (markdown output)
bin/pasta-lunch src/

# HTML output
bin/pasta-lunch --format=html > report.html

# Custom exclude patterns
bin/pasta-lunch src --exclude="*Module.php,*Test.php"

# No exclusions
bin/pasta-lunch src --no-exclude
```

Note: Requires PHPMD installed (`composer require --dev phpmd/phpmd`).

## Architecture

This is a single-file CLI tool (`bin/pasta-lunch`) with no src directory or tests. The script:

1. Invokes PHPMD with `codesize,design` rulesets
2. Parses PHPMD text output to extract metrics
3. Categorizes files into 4 severity levels based on thresholds
4. Generates markdown or HTML reports

### Key Metrics and Thresholds

| Metric | Piccolo | Medio | Grande | Mamma Mia! |
|--------|---------|-------|--------|------------|
| CyclomaticComplexity (CC) | ≤10 | 11-15 | 16-20 | 21+ |
| NPathComplexity | ≤50 | 51-200 | 201-500 | 501+ |
| CouplingBetweenObjects (CBO) | ≤10 | 11-13 | 14-17 | 18+ |
| ExcessiveClassComplexity (ECC) | ≤50 | 51-80 | 81-100 | 101+ |

### Claude Code Skill

The project includes a Claude Code skill in `skills/pasta-lunch/SKILL.md` that provides:
- PHPMD execution instructions
- Metric interpretation guidance
- Output formatting templates
- Common refactoring patterns for each metric type
