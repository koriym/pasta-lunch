---
layout: default
nav_exclude: true
title: CouplingBetweenObjects (CBO)
---

# CouplingBetweenObjects (CBO)

Emitted when a class depends on too many other classes. High coupling makes code harder to test, maintain, and reuse.

```php
<?php
class Import extends ResourceObject
{
    use ResourceInject;
    use AuraSqlInject;

    public function __construct(
        private readonly ArticleRepository $articleRepo,
        private readonly ImageRepository $imageRepo,
        private readonly TagRepository $tagRepo,
        private readonly CategoryRepository $categoryRepo,
        private readonly AuthorRepository $authorRepo,
        private readonly Validator $validator,
        private readonly ImageProcessor $imageProcessor,
        private readonly NotificationService $notifier,
        // ... 22 dependencies
    ) {}

    public function onPost(): static
    {
        // 400 lines of business logic
        $this->validate();
        $this->processImages();
        $this->saveTags();
        $this->saveCategories();
        $this->notify();
        // ...
        return $this;
    }
}
```

## Why this is bad

- **Hard to test**: Need to mock 22 dependencies
- **Hard to understand**: Too many responsibilities in one class
- **Fragile**: Changes in any dependency may break this class
- **Violates Single Responsibility Principle**: Class is doing too much

## How to fix

Extract business logic to a dedicated service class:

```php
<?php
class Import extends ResourceObject
{
    public function __construct(
        private readonly ImportService $service,
    ) {}

    public function onPost(string $id): static
    {
        $this->body = $this->service->import($id);

        return $this;
    }
}
```

The resource class now has a single responsibility: deciding what to return. All business logic is delegated to `ImportService`.

## Threshold

| Level | CBO Value |
|-------|-----------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-13 |
| 🍝🍝🍝 Grande | 14-17 |
| 🍝🍝🍝🍝 Mamma Mia! | 18+ |
