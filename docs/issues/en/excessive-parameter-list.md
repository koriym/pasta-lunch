---
layout: default
nav_exclude: true
title: ExcessiveParameterList
---

# ExcessiveParameterList

Emitted when a method has too many parameters, indicating the method is doing too much or needs a parameter object.

```php
<?php
public function createUser(
    string $firstName,
    string $lastName,
    string $email,
    string $password,
    string $phone,
    string $address,
    string $city,
    string $country,
    string $postalCode,
    bool $newsletter
): User {
    // ...
}
```

## Why this is bad

- **Hard to call**: Easy to mix up parameter order
- **Hard to read**: Method signature is overwhelming
- **Hard to extend**: Adding parameters makes it worse
- **Sign of SRP violation**: Method likely does too much

## How to fix

Use a parameter object or DTO:

```php
<?php
class CreateUserRequest
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $password,
        public readonly Address $address,
        public readonly bool $newsletter = false,
    ) {}
}

public function createUser(CreateUserRequest $request): User
{
    // Clean and focused
}
```

## Threshold

| Level | Parameters |
|-------|------------|
| 🍝 Piccolo | ≤5 |
| 🍝🍝 Medio | 6-10 |
| 🍝🍝🍝 Grande | 11-15 |
| 🍝🍝🍝🍝 Mamma Mia! | 16+ |
