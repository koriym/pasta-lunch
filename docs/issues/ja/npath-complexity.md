---
layout: default
nav_exclude: true
title: NPathComplexity
lang: ja
---

# NPathComplexity

メソッドの実行パスが多すぎる場合に発生します。NPathは各条件の結果の積であり、条件が増えると指数的に増加します。

```php
<?php
public function validate(array $data): bool
{
    if (isset($data['name'])) {           // 2パス
        if (strlen($data['name']) > 0) {   // 2パス
            if (isset($data['email'])) {   // 2パス
                if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {  // 2パス
                    if (isset($data['age'])) {  // 2パス
                        if ($data['age'] >= 18) {  // 2パス
                            return true;
                        }
                    }
                }
            }
        }
    }
    return false;
    // NPath = 2 × 2 × 2 × 2 × 2 × 2 = 64パス
}
```

## なぜ問題か

- **テストケースが指数的に増加**: すべてのパスをテストする必要がある
- **深いネスト**: ロジックが追いにくい
- **隠れた条件**: 失敗ケースを見落としやすい
- **保守が困難**: 新しい条件を追加するとパス数が倍増

## 修正方法

早期リターン（ガード句）で構造をフラットにする：

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
    // NPath = 7パス（指数的ではなく線形）
}
```

## しきい値

| レベル | NPath値 |
|--------|---------|
| 🍝 Piccolo | ≤50 |
| 🍝🍝 Medio | 51-200 |
| 🍝🍝🍝 Grande | 201-500 |
| 🍝🍝🍝🍝 Mamma Mia! | 501+ |
