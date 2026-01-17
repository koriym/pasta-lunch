# Pasta Lunch 🍝

Detect spaghetti code using PHPMD metrics.

## Requirements

- PHP 8.1+
- PHPMD (`composer require --dev phpmd/phpmd`)

## Installation

### As Claude Code Skill

```bash
claude mcp add-skill koriym/pasta-lunch
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

# Direct execution (default: markdown, --format=html for HTML)
./vendor/bin/pasta-lunch src/Resource
./vendor/bin/pasta-lunch --format=html > report.html

# Custom exclude patterns (default: *Module.php)
./vendor/bin/pasta-lunch src --exclude="*Module.php,*Test.php"

# No exclusions
./vendor/bin/pasta-lunch src --no-exclude
```

## Composer Scripts

Add to your project's `composer.json`:

```json
{
    "scripts": {
        "pasta": "pasta-lunch",
        "pasta:html": "pasta-lunch --format=html"
    }
}
```

## Spaghetti Levels

| | Level | Meaning |
|---|-------|---------|
| 🍝 | Piccolo | Clean code |
| 🍝🍝 | Normale | Acceptable |
| 🍝🍝🍝 | Grande | Refactoring required |
| 🍝🍝🍝🍝 | Mamma Mia! | Unmaintainable |

## Metrics

| Metric | Piccolo | Normale | Grande | Mamma Mia! |
|--------|---------|---------|--------|------------|
| CyclomaticComplexity (CC) | ≤10 | 11-15 | 16-20 | 21+ |
| NPathComplexity | ≤50 | 51-200 | 201-500 | 501+ |
| CouplingBetweenObjects (CBO) | ≤10 | 11-13 | 14-17 | 18+ |
| ExcessiveClassComplexity (ECC) | ≤50 | 51-80 | 81-100 | 101+ |

## License

[MIT](LICENSE)
