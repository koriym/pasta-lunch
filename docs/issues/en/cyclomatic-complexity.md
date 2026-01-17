---
layout: default
nav_exclude: true
title: CyclomaticComplexity (CC)
---

# CyclomaticComplexity (CC)

Emitted when a method has too many decision points (if, else, switch, for, while, catch, etc.).

```php
<?php
public function process(string $type, array $data): array
{
    if ($type === 'article') {
        if (isset($data['draft'])) {
            if ($data['draft'] === true) {
                // ...
            } else {
                // ...
            }
        } elseif (isset($data['scheduled'])) {
            // ...
        }
    } elseif ($type === 'page') {
        if ($data['template'] === 'full') {
            // ...
        } elseif ($data['template'] === 'sidebar') {
            // ...
        }
    } elseif ($type === 'widget') {
        // ...
    }
    // CC = 15
}
```

## Why this is bad

- **Hard to test**: Need many test cases to cover all paths
- **Hard to read**: Deep nesting obscures the logic
- **Bug prone**: Easy to miss edge cases
- **Hard to modify**: Adding new conditions increases complexity exponentially

## How to fix

Replace conditionals with polymorphism using the Strategy pattern:

```php
<?php
public function process(string $type, array $data): array
{
    $processor = $this->processorFactory->create($type);

    return $processor->process($data);
}
```

Each processor handles its own logic:

```php
<?php
class ArticleProcessor implements ProcessorInterface
{
    public function process(array $data): array
    {
        // Only article-specific logic here
    }
}
```

## Threshold

| Level | CC Value |
|-------|----------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-15 |
| 🍝🍝🍝 Grande | 16-20 |
| 🍝🍝🍝🍝 Mamma Mia! | 21+ |
