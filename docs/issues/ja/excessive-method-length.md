---
layout: default
nav_exclude: true
title: ExcessiveMethodLength
lang: ja
---

# ExcessiveMethodLength

メソッドの行数が多すぎる場合に発生します。理解や保守が困難になります。

```php
<?php
public function processOrder(array $data): array
{
    // 1-10行目: 入力検証
    // 11-30行目: 決済処理
    // 31-50行目: 在庫更新
    // 51-70行目: 通知送信
    // 71-90行目: レポート生成
    // 91-100行目: クリーンアップ
    // ... 100行以上のコード
}
```

## なぜ問題か

- **理解が困難**: 1箇所にロジックが集中しすぎ
- **テストが困難**: すべてのパスをカバーするのに複雑なセットアップが必要
- **再利用が困難**: ロジックが密結合している
- **保守が困難**: 変更が関係ないコードを壊すリスク

## 修正方法

論理的なセクションを別メソッドに抽出:

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
    // 検証ロジックに集中
}

private function processPayment(array $data): Payment
{
    // 決済ロジックに集中
}
```

## 閾値

| レベル | 行数 |
|--------|------|
| 🍝 Piccolo | ≤50 |
| 🍝🍝 Medio | 51-100 |
| 🍝🍝🍝 Grande | 101-150 |
| 🍝🍝🍝🍝 Mamma Mia! | 151+ |
