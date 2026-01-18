---
layout: default
nav_exclude: true
title: TooManyFields
lang: ja
---

# TooManyFields

クラスのフィールド（プロパティ）が多すぎる場合に発生します。クラスの責務が多すぎることを示しています。

```php
<?php
class Order
{
    private int $id;
    private string $status;
    private Customer $customer;
    private Address $billingAddress;
    private Address $shippingAddress;
    private PaymentMethod $payment;
    private array $items;
    private float $subtotal;
    private float $tax;
    private float $shipping;
    private float $discount;
    private float $total;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private ?string $notes;
    // ... さらに多くのフィールド
}
```

## なぜ問題か

- **神クラス**: クラスがすべてをやろうとしている
- **高結合**: 調整すべきものが多すぎる
- **テストが困難**: 複雑な状態管理
- **理解が困難**: 頭に入れておくことが多すぎる

## 修正方法

小さく集中したクラスに分割:

```php
<?php
class Order
{
    public function __construct(
        private readonly OrderId $id,
        private readonly Customer $customer,
        private readonly OrderItems $items,
        private readonly OrderPricing $pricing,
        private OrderStatus $status,
    ) {}
}

class OrderPricing
{
    public function __construct(
        private readonly Money $subtotal,
        private readonly Money $tax,
        private readonly Money $shipping,
        private readonly Money $discount,
    ) {}

    public function total(): Money
    {
        // 集中した計算ロジック
    }
}
```

## 閾値

| レベル | フィールド数 |
|--------|-------------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-15 |
| 🍝🍝🍝 Grande | 16-20 |
| 🍝🍝🍝🍝 Mamma Mia! | 21+ |
