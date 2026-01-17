# Pasta Lunch

Detect spaghetti code using PHPMD metrics.

## Installation

```bash
composer require --dev koriym/pasta-lunch
```

## Usage

```bash
# Markdown output
./vendor/bin/pasta-lunch src/Resource

# HTML output
./vendor/bin/pasta-lunch --format=html > report.html

# Custom exclude patterns
./vendor/bin/pasta-lunch src --exclude="*Module.php,*Test.php"
```

## Spaghetti Levels

| | Level | Meaning |
|---|-------|---------|
| 🍝 | Piccolo | Clean code |
| 🍝🍝 | Normale | Acceptable |
| 🍝🍝🍝 | Grande | Refactoring required |
| 🍝🍝🍝🍝 | Mamma Mia! | Unmaintainable |

## Metrics

| Metric | Threshold | Problem |
|--------|-----------|---------|
| CouplingBetweenObjects (CBO) | 13 | Too many dependencies |
| CyclomaticComplexity (CC) | 10 | Too many branches |
| NPathComplexity | 200 | Too many execution paths |
| ExcessiveClassComplexity | 50 | Class too complex |

## License

MIT
