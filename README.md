# PASTA Lunch 🍝

**P**HP **A**lert for **S**paghetti **T**wisted **A**rchitecture

> Order your code quality check - from Piccolo to Mamma Mia!

Detect spaghetti code using PHPMD metrics.

PASTA focuses on **tangled, complex code** - the kind that's hard to test, hard to change, and hard to understand. These issues aren't caught by type checkers like PHPStan or Psalm, yet they're often what kills project sustainability over time.

Unlike general code quality tools, it specifically targets metrics that indicate code entanglement: cyclomatic complexity, coupling, and structural bloat.

## Requirements

- PHP 8.1+

## Installation

### As Claude Code Plugin

```text
/plugin marketplace add koriym/pasta-lunch
/plugin install pasta-lunch@pasta-lunch
```

### Via Composer

```bash
composer require --dev koriym/pasta-lunch
```

## Usage

```bash
# Via composer script
composer pasta

# HTML output
composer pasta:html > report.html

# Direct execution (default: text with colors)
./vendor/bin/pasta src/Resource
./vendor/bin/pasta --format=md > report.md
./vendor/bin/pasta --format=html > report.html

# Custom exclude patterns (default: *Module.php)
./vendor/bin/pasta src --exclude="*Module.php,*Test.php"

# No exclusions
./vendor/bin/pasta src --no-exclude
```

## Composer Scripts

Add to your project's `composer.json`:

```json
{
    "scripts": {
        "pasta": "bin/pasta",
        "pasta:html": "bin/pasta --format=html"
    }
}
```

## Spaghetti Levels

| | Level | Meaning |
|---|-------|---------|
| 🍝 | Piccolo | Clean code |
| 🍝🍝 | Medio | Acceptable |
| 🍝🍝🍝 | Grande | Refactoring required |
| 🍝🍝🍝🍝 | Mamma Mia! | Unmaintainable |

## Metrics

| Metric | Piccolo | Medio | Grande | Mamma Mia! |
|--------|---------|-------|--------|------------|
| CyclomaticComplexity (CC) | ≤10 | 11-15 | 16-20 | 21+ |
| NPathComplexity | ≤100 | 101-200 | 201-500 | 501+ |
| CouplingBetweenObjects (CBO) | ≤10 | 11-15 | 16-20 | 21+ |
| ExcessiveClassComplexity (ECC) | ≤50 | 51-80 | 81-120 | 121+ |
| ExcessiveMethodLength | ≤50 | 51-100 | 101-150 | 151+ |
| ExcessiveParameterList | ≤5 | 6-10 | 11-15 | 16+ |
| TooManyFields | ≤10 | 11-15 | 16-20 | 21+ |
| TooManyPublicMethods | ≤10 | 11-15 | 16-20 | 21+ |

See [Issue Types](https://koriym.github.io/pasta-lunch/) for detailed documentation.

### Why these thresholds?

PASTA thresholds are designed around **clean code ideals**, not just "acceptable" levels. PHPMD defaults represent the point where "measures should be taken" - meaning code at those thresholds already needs attention.

| Metric | PHPMD Default | PASTA Piccolo | Rationale |
|--------|---------------|---------------------|-----------|
| NPath | 200 | ≤100 | 200 paths exceeds human cognitive capacity |
| CBO | 13 | ≤10 | Proper DI keeps coupling under 10 |
| MethodLength | 100 | ≤50 | Modern standards favor 30-50 lines |

- **Piccolo** = Clean, maintainable code
- **Medio** = Approaching PHPMD thresholds
- **Grande** = At or beyond PHPMD "action required" level
- **Mamma Mia!** = Critical, immediate refactoring needed

## License

[MIT](LICENSE)
