---
layout: default
nav_exclude: true
title: TooManyPublicMethods
lang: ja
---

# TooManyPublicMethods

クラスが公開するpublicメソッドが多すぎる場合に発生します。クラスの責務が多すぎるか、インターフェースが肥大化しています。

```php
<?php
class UserService
{
    public function create(): void {}
    public function update(): void {}
    public function delete(): void {}
    public function find(): void {}
    public function findAll(): void {}
    public function authenticate(): void {}
    public function authorize(): void {}
    public function sendEmail(): void {}
    public function resetPassword(): void {}
    public function updateProfile(): void {}
    public function uploadAvatar(): void {}
    public function exportData(): void {}
    // ... さらに多くのpublicメソッド
}
```

## なぜ問題か

- **SRP違反**: クラスがやることが多すぎる
- **大きなインターフェース**: クライアントが理解しづらい
- **モックが困難**: テストに多くのスタブが必要
- **保守負担**: 変更がコードベース全体に波及

## 修正方法

責務ごとに集中したサービスに分割:

```php
<?php
class UserRepository
{
    public function find(UserId $id): ?User {}
    public function save(User $user): void {}
    public function delete(User $user): void {}
}

class AuthenticationService
{
    public function authenticate(Credentials $credentials): User {}
    public function resetPassword(Email $email): void {}
}

class UserProfileService
{
    public function updateProfile(User $user, ProfileData $data): void {}
    public function uploadAvatar(User $user, UploadedFile $file): void {}
}
```

## 閾値

| レベル | Publicメソッド数 |
|--------|-----------------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-15 |
| 🍝🍝🍝 Grande | 16-20 |
| 🍝🍝🍝🍝 Mamma Mia! | 21+ |
