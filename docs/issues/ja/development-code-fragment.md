---
layout: default
nav_exclude: true
title: DevelopmentCodeFragment
lang: ja
---

# DevelopmentCodeFragment

開発時のみ使用すべきデバッグコードが本番コードに含まれている場合に発生します。

```php
<?php
class Import extends ResourceObject
{
    public function onPost(array $data): static
    {
        $result = $this->process($data);

        // デバッグコード: 本番に残っている
        var_dump($result);
        print_r($data);
        error_log('Import completed: ' . json_encode($result));

        $this->body = $result;

        return $this;
    }
}
```

## なぜ問題か

- **情報漏洩**: 内部データ構造が露出する可能性
- **パフォーマンス低下**: 不要なI/O操作
- **ユーザー体験の悪化**: デバッグ出力がユーザーに見える
- **ログ汚染**: ノイズでログが埋まる
- **セキュリティリスク**: スタックトレースが実装詳細を暴露

## 修正方法

デバッグ文を削除し、適切なロギングを使用する：

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

        // 適切なロギングを使用
        $this->logger->info('Import completed', ['id' => $result['id']]);

        $this->body = $result;

        return $this;
    }
}
```

## 検出される関数

- `var_dump()`
- `print_r()`
- `error_log()`
- `debug_print_backtrace()`
- `debug_zval_dump()`

## 予防策

pre-commitフックまたはCIチェックを追加する：

```bash
git diff --cached --name-only | xargs grep -l 'var_dump\|print_r' && exit 1
```
