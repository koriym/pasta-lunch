---
layout: default
nav_exclude: true
title: DevelopmentCodeFragment
---

# DevelopmentCodeFragment

Emitted when debug code that should only be used during development is found in production code.

```php
<?php
class Import extends ResourceObject
{
    public function onPost(array $data): static
    {
        $result = $this->process($data);

        // DEBUG CODE: Left in production
        var_dump($result);
        print_r($data);
        error_log('Import completed: ' . json_encode($result));

        $this->body = $result;

        return $this;
    }
}
```

## Why this is bad

- **Information disclosure**: May expose sensitive data
- **Performance impact**: Unnecessary I/O operations
- **Unprofessional**: Users may see debug output
- **Log pollution**: Fills logs with noise
- **Security risk**: Stack traces may reveal implementation details

## How to fix

Remove all debug statements before deployment:

```php
<?php
class Import extends ResourceObject
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function onPost(array $data): static
    {
        $result = $this->process($data);

        // Use proper logging instead
        $this->logger->info('Import completed', ['id' => $result['id']]);

        $this->body = $result;

        return $this;
    }
}
```

## Detected Functions

- `var_dump()`
- `print_r()`
- `error_log()`
- `debug_print_backtrace()`
- `debug_zval_dump()`

## Prevention

Add a pre-commit hook or CI check:

```bash
git diff --cached --name-only | xargs grep -l 'var_dump\|print_r' && exit 1
```
