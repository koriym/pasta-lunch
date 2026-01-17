---
layout: default
title: Home
---

# Spaghetti Code Detection

Detect tangled, complex code using PHPMD metrics.

## Spaghetti Levels

| | Level | Meaning |
|---|-------|---------|
| 🍝 | Piccolo | 軽くて消化しやすい |
| 🍝🍝 | Medio | 標準的な一皿 |
| 🍝🍝🍝 | Grande | お腹いっぱい |
| 🍝🍝🍝🍝 | Mamma Mia! | 食べきれない！ |

## Issue Types

### Coupling

- [CouplingBetweenObjects](issues/en/coupling-between-objects) - Too many dependencies

### Complexity

- [CyclomaticComplexity](issues/en/cyclomatic-complexity) - Too many branches
- [NPathComplexity](issues/en/npath-complexity) - Too many execution paths
- [ExcessiveClassComplexity](issues/en/excessive-class-complexity) - Class too complex

### Debug Code

- [DevelopmentCodeFragment](issues/en/development-code-fragment) - Debug code in production

## Quick Start

```bash
php -d error_reporting=E_ERROR \
  ./vendor/bin/phpmd src/Resource,src/Service,src/Helper text codesize,design
```

## References

- [PHPMD Code Size Rules](https://phpmd.org/rules/codesize.html)
- [PHPMD Design Rules](https://phpmd.org/rules/design.html)
