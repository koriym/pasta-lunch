---
layout: default
nav_exclude: true
title: NPathComplexity
---

# NPathComplexity

Emitted when a method has too many possible execution paths. NPath is the product of all decision outcomes, growing exponentially with each condition.

```php
<?php
public function validate(array $data): bool
{
    if (isset($data['name'])) {           // 2 paths
        if (strlen($data['name']) > 0) {   // 2 paths
            if (isset($data['email'])) {   // 2 paths
                if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {  // 2 paths
                    if (isset($data['age'])) {  // 2 paths
                        if ($data['age'] >= 18) {  // 2 paths
                            return true;
                        }
                    }
                }
            }
        }
    }
    return false;
    // NPath = 2 × 2 × 2 × 2 × 2 × 2 = 64 paths
}
```

## Why this is bad

- **Exponential test cases**: Need to test all possible paths
- **Deep nesting**: Hard to follow the logic
- **Hidden conditions**: Easy to miss failure cases
- **Maintenance nightmare**: Each new condition doubles the paths

## How to fix

Use early returns (guard clauses) to flatten the structure:

```php
<?php
public function validate(array $data): bool
{
    if (!isset($data['name'])) {
        return false;
    }
    if (strlen($data['name']) === 0) {
        return false;
    }
    if (!isset($data['email'])) {
        return false;
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (!isset($data['age'])) {
        return false;
    }
    if ($data['age'] < 18) {
        return false;
    }

    return true;
    // NPath = 7 paths (linear, not exponential)
}
```

## Threshold

| Level | NPath Value |
|-------|-------------|
| 🍝 Piccolo | ≤50 |
| 🍝🍝 Medio | 51-200 |
| 🍝🍝🍝 Grande | 201-500 |
| 🍝🍝🍝🍝 Mamma Mia! | 501+ |
