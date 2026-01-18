---
layout: default
title: Home
---

# Pasta Lunch

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

## Quick Start

```bash
composer require --dev koriym/pasta-lunch
./vendor/bin/pasta-lunch src/
```

## References

- [PHPMD Code Size Rules](https://phpmd.org/rules/codesize.html)
- [PHPMD Design Rules](https://phpmd.org/rules/design.html)
