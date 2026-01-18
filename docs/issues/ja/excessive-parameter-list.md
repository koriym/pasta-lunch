---
layout: default
nav_exclude: true
title: ExcessiveParameterList
lang: ja
---

# ExcessiveParameterList

メソッドのパラメータが多すぎる場合に発生します。メソッドがやりすぎているか、パラメータオブジェクトが必要です。

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

## なぜ問題か

- **呼び出しが困難**: パラメータの順序を間違えやすい
- **読みにくい**: メソッドシグネチャが圧倒的
- **拡張が困難**: パラメータ追加で悪化する
- **SRP違反の兆候**: メソッドがやりすぎている可能性

## 修正方法

パラメータオブジェクトまたはDTOを使用:

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
    // クリーンで集中したコード
}
```

## 閾値

| レベル | パラメータ数 |
|--------|-------------|
| 🍝 Piccolo | ≤5 |
| 🍝🍝 Medio | 6-10 |
| 🍝🍝🍝 Grande | 11-15 |
| 🍝🍝🍝🍝 Mamma Mia! | 16+ |
