---
layout: default
nav_exclude: true
title: TooManyPublicMethods
---

# TooManyPublicMethods

Emitted when a class exposes too many public methods, indicating the class has too many responsibilities or a bloated interface.

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
    // ... more public methods
}
```

## Why this is bad

- **SRP violation**: Class does too many things
- **Large interface**: Hard for clients to understand
- **Hard to mock**: Testing requires many stubs
- **Maintenance burden**: Changes ripple across codebase

## How to fix

Split by responsibility into focused services:

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

## Threshold

| Level | Public Methods |
|-------|----------------|
| 🍝 Piccolo | ≤10 |
| 🍝🍝 Medio | 11-15 |
| 🍝🍝🍝 Grande | 16-20 |
| 🍝🍝🍝🍝 Mamma Mia! | 21+ |
