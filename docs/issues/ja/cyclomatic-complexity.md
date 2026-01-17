---
layout: default
nav_exclude: true
title: CyclomaticComplexity (CC)
lang: ja
---

# CyclomaticComplexity (CC)

メソッドに分岐（if、else、switch、for、while、catchなど）が多すぎる場合に発生します。

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

## なぜ問題か

- **テストが困難**: すべてのパスをカバーするために多くのテストケースが必要
- **読みにくい**: 深いネストがロジックを分かりにくくする
- **バグが発生しやすい**: エッジケースを見落としやすい
- **変更が困難**: 新しい条件を追加すると複雑度が指数的に増加

## 修正方法

Strategyパターンで条件分岐をポリモーフィズムに置き換える：

```php
<?php
public function process(string $type, array $data): array
{
    $processor = $this->processorFactory->create($type);

    return $processor->process($data);
}
```

各プロセッサは自分のロジックだけを処理する：

```php
<?php
class ArticleProcessor implements ProcessorInterface
{
    public function process(array $data): array
    {
        // article固有のロジックのみ
    }
}
```

## しきい値

| レベル | CC値 |
|--------|------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-15 |
| 🍝🍝🍝 Grande | 16-20 |
| 🍝🍝🍝🍝 Mamma Mia! | 21+ |
