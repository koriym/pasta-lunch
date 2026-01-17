---
layout: default
nav_exclude: true
title: CouplingBetweenObjects (CBO)
lang: ja
---

# CouplingBetweenObjects (CBO)

クラスが多くの他クラスに依存している場合に発生します。高い結合度はテスト、保守、再利用を困難にします。

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
        // ... 22個の依存
    ) {}

    public function onPost(): static
    {
        // 400行のビジネスロジック
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

## なぜ問題か

- **テストが困難**: 22個の依存をモックする必要がある
- **理解が困難**: 1つのクラスに責務が多すぎる
- **壊れやすい**: どの依存の変更もこのクラスに影響する
- **単一責任の原則に違反**: クラスがやりすぎている

## 修正方法

ビジネスロジックを専用のサービスクラスに抽出する：

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

リソースクラスは「何を返すか」を決めるだけの単一責任になります。すべてのビジネスロジックは`ImportService`に委譲されます。

## しきい値

| レベル | CBO値 |
|--------|-------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-13 |
| 🍝🍝🍝 Grande | 14-17 |
| 🍝🍝🍝🍝 Mamma Mia! | 18+ |
