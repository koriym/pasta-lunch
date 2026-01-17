---
layout: default
nav_exclude: true
title: ExcessiveClassComplexity (ECC)
lang: ja
---

# ExcessiveClassComplexity (ECC)

クラス内のすべてのメソッドの複雑度合計がしきい値を超えた場合に発生します。これはすべてのメソッドの循環的複雑度の合計です。

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
    // 合計 ECC = 95
}
```

## なぜ問題か

- **神クラス**: クラスがやりすぎている
- **理解が困難**: 認知負荷が高すぎる
- **テストの負担**: 広範なテストスイートが必要
- **マージコンフリクト**: 複数の開発者が同じファイルを触る
- **硬直した設計**: 部分的な再利用が困難

## 修正方法

責任ごとにクラスを分割する：

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

ビジネスロジックはサービスクラスに移動：

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
        // 集中した責任
    }
}
```

## しきい値

| レベル | ECC値 |
|--------|-------|
| 🍝 Piccolo | ≤50 |
| 🍝🍝 Medio | 51-80 |
| 🍝🍝🍝 Grande | 81-100 |
| 🍝🍝🍝🍝 Mamma Mia! | 101+ |
