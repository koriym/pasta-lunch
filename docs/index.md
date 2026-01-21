---
layout: default
title: Home
---

# PASTA Lunch

**P**HP **A**lert for **S**paghetti **T**wisted **A**rchitecture

> Order your code quality check - from Piccolo to Mamma Mia!

Detect spaghetti code using PHPMD metrics.

## Issue Types

### Coupling

- [CouplingBetweenObjects (CBO)](issues/en/coupling-between-objects) - Too many dependencies in a class

### Complexity

- [CyclomaticComplexity (CC)](issues/en/cyclomatic-complexity) - Too many branches in a method
- [NPathComplexity](issues/en/npath-complexity) - Too many execution paths
- [ExcessiveClassComplexity (ECC)](issues/en/excessive-class-complexity) - Overall class is too complex

### Size

- [ExcessiveMethodLength](issues/en/excessive-method-length) - Method has too many lines
- [ExcessiveParameterList](issues/en/excessive-parameter-list) - Too many parameters
- [TooManyFields](issues/en/too-many-fields) - Class has too many fields
- [TooManyPublicMethods](issues/en/too-many-public-methods) - Class exposes too many methods

### Debug Code

- [DevelopmentCodeFragment](issues/en/development-code-fragment) - Debug code left in production

## Installation

```bash
composer require --dev koriym/pasta-lunch
```

## Usage

```bash
# Text output (default)
./vendor/bin/pasta src/

# Markdown output
./vendor/bin/pasta --format=md > report.md

# HTML output
./vendor/bin/pasta --format=html > report.html

# Custom exclude patterns (default: *Module.php)
./vendor/bin/pasta src --exclude="*Module.php,*Test.php"
```

See [Sample Report](demo/report.html) for an example HTML output.

For full documentation, see the [README](https://github.com/koriym/pasta-lunch#readme).

## References

- [GitHub Repository](https://github.com/koriym/pasta-lunch)
- [PHPMD Code Size Rules](https://phpmd.org/rules/codesize.html)
- [PHPMD Design Rules](https://phpmd.org/rules/design.html)
