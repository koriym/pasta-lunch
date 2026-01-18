---
layout: default
nav_exclude: true
title: TooManyFields
---

# TooManyFields

Emitted when a class has too many fields (properties), indicating the class has too many responsibilities.

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
    // ... more fields
}
```

## Why this is bad

- **God class**: Class is trying to do everything
- **High coupling**: Too many things to coordinate
- **Hard to test**: Complex state management
- **Hard to understand**: Too much to keep in mind

## How to fix

Split into smaller, focused classes:

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
        // Focused calculation
    }
}
```

## Threshold

| Level | Fields |
|-------|--------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-15 |
| 🍝🍝🍝 Grande | 16-20 |
| 🍝🍝🍝🍝 Mamma Mia! | 21+ |
