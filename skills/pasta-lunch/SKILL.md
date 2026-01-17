---
name: pasta-lunch
description: Detect spaghetti code using PHPMD. Measures CBO (coupling), CC (cyclomatic complexity), and NPath to identify refactoring targets.
---

# Spaghetti Code Detection

Detect tangled, complex code using PHPMD metrics.

## Execution

### 1. Run PHPMD

Target directories (exclude Module - it's DI configuration, not business logic):

```bash
php -d error_reporting=E_ERROR ./vendor/bin/phpmd src/Resource,src/Service,src/Helper,src/Domain text codesize,design 2>/dev/null
```

For specific directory:
```bash
php -d error_reporting=E_ERROR ./vendor/bin/phpmd src/Resource text codesize,design 2>/dev/null
```

**Excluded by default:**
- `src/Module/` - DI configuration files (high coupling is expected)
- `src/Exception/` - Simple exception classes

### 2. Key Metrics

| Metric | Meaning | Threshold | Problem |
|--------|---------|-----------|---------|
| **CouplingBetweenObjects (CBO)** | Number of dependencies | 13 | Too many responsibilities, needs Service extraction |
| **CyclomaticComplexity (CC)** | Branching complexity | 10 | Logic should be delegated to Domain layer |
| **NPathComplexity** | Execution paths | 200 | Hard to test, bug-prone |
| **ExcessiveClassComplexity** | Overall class complexity | 50 | Class needs splitting |

### 3. Spaghetti Levels (4-Grade Scale)

| | Level | Meaning |
|---|-------|---------|
| 🍝 | Piccolo | 軽くて消化しやすい |
| 🍝🍝 | Normale | 標準的な一皿 |
| 🍝🍝🍝 | Grande | お腹いっぱい |
| 🍝🍝🍝🍝 | Mamma Mia! | 食べきれない！要リファクタリング |

Grade thresholds:
- **CBO**: Piccolo(≤10), Normale(11-13), Grande(14-17), Mamma Mia!(18+)
- **CC**: Piccolo(≤7), Normale(8-10), Grande(11-15), Mamma Mia!(16+)
- **NPath**: Piccolo(≤100), Normale(101-200), Grande(201-500), Mamma Mia!(501+)
- **ECC**: Piccolo(≤30), Normale(31-50), Grande(51-80), Mamma Mia!(81+)

### 4. Output Format

Present results in two parts:

#### Part 1: Summary Table

```
## 🍝 Spaghetti Code Detection Results

| File | Level | Comment |
|------|-------|---------|
| Service/OrderService.php | 🍝🍝🍝🍝 Mamma Mia! | 複数の責務が混在しています |
| Helper/DateHelper.php | 🍝🍝🍝 Grande | 処理が集中しています |
| Resource/App/User.php | 🍝🍝 Normale | 許容範囲内です |
| Resource/App/Index.php | 🍝 Piccolo | きれいに整理されています |
```

#### Part 2: File Details (Mamma Mia! only)

For files with Mamma Mia! level, provide details:

```
### Service/OrderService.php 🍝🍝🍝🍝 Mamma Mia!

**Issues:**
- ExcessiveClassComplexity: 73 (threshold: 50)
- CyclomaticComplexity: 15 in `processOrder()` (threshold: 10)
- NPathComplexity: 444 in `validateOrderItems()` (threshold: 200)

**Recommendation:**
検索パラメータの変換処理を `OrderProcessor` クラスに抽出してください。
```

#### Part 3: Legend and References

```
### Legend

| | Level |
|---|-------|
| 🍝 | Piccolo - 軽くて消化しやすい |
| 🍝🍝 | Normale - 標準的な一皿 |
| 🍝🍝🍝 | Grande - お腹いっぱい |
| 🍝🍝🍝🍝 | Mamma Mia! - 食べきれない！ |

### References

- [CouplingBetweenObjects](https://koriym.github.io/pasta-lunch/issues/en/coupling-between-objects)
- [CyclomaticComplexity](https://koriym.github.io/pasta-lunch/issues/en/cyclomatic-complexity)
- [NPathComplexity](https://koriym.github.io/pasta-lunch/issues/en/npath-complexity)
- [ExcessiveClassComplexity](https://koriym.github.io/pasta-lunch/issues/en/excessive-class-complexity)
- [DevelopmentCodeFragment](https://koriym.github.io/pasta-lunch/issues/en/development-code-fragment)
```

## Fix Patterns

### High CBO (Coupling)

```php
// Before: CBO=22
class Import extends ResourceObject
{
    use ResourceInject;
    use AuraSqlInject;
    // 22 dependencies...

    public function onPost(): ResourceObject
    {
        // 400 lines of logic
    }
}

// After: CBO=3
class Import extends ResourceObject
{
    public function __construct(
        private readonly ImportService $service,
    ) {}

    public function onPost(string $id): static
    {
        $this->body = $this->service->import($id);
        return $this;
    }
}
```

**Key points:**
- Replace traits (ResourceInject, AuraSqlInject) with constructor injection
- Delegate logic to Service/Domain
- Resource only decides "what to return"

### High CC (Cyclomatic Complexity)

```php
// Before: CC=15
public function process(string $type): void
{
    if ($type === 'a') {
        // ...
    } elseif ($type === 'b') {
        // ...
    } elseif ($type === 'c') {
        // ...
    }
}

// After: CC=2
public function process(string $type): void
{
    $processor = $this->processorFactory->create($type);
    $processor->execute();
}
```

**Key points:**
- Replace conditionals with Strategy pattern
- Use factory to select implementation
- Each implementation has single responsibility

### High NPath (Execution Paths)

```php
// Before: NPath=500
public function validate(array $data): bool
{
    if (isset($data['a'])) {
        if ($data['a'] > 0) {
            if (isset($data['b'])) {
                // Deep nesting...
            }
        }
    }
    return true;
}

// After: NPath=10
public function validate(array $data): bool
{
    if (!isset($data['a'])) {
        return false;
    }
    if ($data['a'] <= 0) {
        return false;
    }
    if (!isset($data['b'])) {
        return false;
    }
    return true;
}
```

**Key points:**
- Use early returns (guard clauses) to reduce nesting
- Make each condition independent
- Extract complex conditions to methods

## Debug Code Detection

PHPMD's DevelopmentCodeFragment rule detects:
- `var_dump()`
- `print_r()`
- `error_log()`

These should not be deployed to production.

## References

- [PHPMD Code Size Rules](https://phpmd.org/rules/codesize.html)
- [PHPMD Design Rules](https://phpmd.org/rules/design.html)
