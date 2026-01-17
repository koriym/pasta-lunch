---
layout: default
nav_exclude: true
title: ExcessiveClassComplexity (ECC)
---

# ExcessiveClassComplexity (ECC)

Emitted when the total complexity of all methods in a class exceeds the threshold. This is the sum of cyclomatic complexity across all methods.

```php
<?php
class ArticleWidget extends ResourceObject
{
    public function onGet(): static { /* CC=12 */ }
    public function onPost(): static { /* CC=15 */ }
    public function onPut(): static { /* CC=18 */ }
    public function onDelete(): static { /* CC=8 */ }
    private function validate(): void { /* CC=10 */ }
    private function processImages(): void { /* CC=12 */ }
    private function updateCache(): void { /* CC=8 */ }
    private function notify(): void { /* CC=6 */ }
    private function log(): void { /* CC=6 */ }
    // Total ECC = 95
}
```

## Why this is bad

- **God class**: Class is doing too many things
- **Hard to understand**: Too much cognitive load
- **Testing burden**: Need extensive test suites
- **Merge conflicts**: Multiple developers touching same file
- **Rigid design**: Hard to reuse parts independently

## How to fix

Split the class by responsibility:

```php
<?php
class ArticleWidget extends ResourceObject
{
    public function __construct(
        private readonly ArticleWidgetService $service,
    ) {}

    public function onGet(string $id): static
    {
        $this->body = $this->service->get($id);

        return $this;
    }

    public function onPost(#[Valid] Article $article): static
    {
        $this->body = $this->service->create($article);

        return $this;
    }
}
```

Business logic moves to service classes:

```php
<?php
class ArticleWidgetService
{
    public function __construct(
        private readonly ArticleRepository $repository,
        private readonly ImageProcessor $imageProcessor,
        private readonly CacheManager $cache,
    ) {}

    public function create(Article $article): array
    {
        // Focused responsibility
    }
}
```

## Threshold

| Level | ECC Value |
|-------|-----------|
| 🍝 Piccolo | ≤50 |
| 🍝🍝 Medio | 51-80 |
| 🍝🍝🍝 Grande | 81-100 |
| 🍝🍝🍝🍝 Mamma Mia! | 101+ |
