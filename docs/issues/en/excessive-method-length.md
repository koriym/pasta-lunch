---
layout: default
nav_exclude: true
title: ExcessiveMethodLength
---

# ExcessiveMethodLength

Emitted when a method has too many lines of code, making it hard to understand and maintain.

```php
<?php
public function processOrder(array $data): array
{
    // Line 1-10: Validate input
    // Line 11-30: Process payment
    // Line 31-50: Update inventory
    // Line 51-70: Send notifications
    // Line 71-90: Generate reports
    // Line 91-100: Cleanup
    // ... 100+ lines of code
}
```

## Why this is bad

- **Hard to understand**: Too much logic in one place
- **Hard to test**: Need complex setup to cover all paths
- **Hard to reuse**: Logic is tightly coupled
- **Hard to maintain**: Changes risk breaking unrelated code

## How to fix

Extract logical sections into separate methods:

```php
<?php
public function processOrder(array $data): array
{
    $this->validateInput($data);
    $payment = $this->processPayment($data);
    $this->updateInventory($data);
    $this->sendNotifications($data);

    return $this->generateResult($payment);
}

private function validateInput(array $data): void
{
    // Focused validation logic
}

private function processPayment(array $data): Payment
{
    // Focused payment logic
}
```

## Threshold

| Level | Lines |
|-------|-------|
| 🍝 Piccolo | ≤50 |
| 🍝🍝 Medio | 51-100 |
| 🍝🍝🍝 Grande | 101-150 |
| 🍝🍝🍝🍝 Mamma Mia! | 151+ |
