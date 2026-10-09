# フロー B — Bearer フロー（サーバー間）

サーバーサイドから直接 API を呼ぶフローです。JS 不要でシンプルに実装できます。

## ファイル構成

```
flow-b/
  form.html   — 問い合わせフォーム（通常の HTML フォーム）
  submit.php  — フォーム受信・スパム判定・メール送信
```

## セットアップ

`submit.php` の設定値を編集する：

```php
define('CONTAICT_SECRET_KEY', 'sk_xxxxxxxxxxxx'); // サーバーキー
define('MAIL_TO', 'admin@example.com');
define('SPAM_THRESHOLD', 70);
```

## 動作の流れ

1. ユーザーがフォームを送信
2. `submit.php` がフォームデータを受信
3. サーバーキーで `/v1/analyze` を呼びスコアを取得（本文と送信者の IP `client_ip` を送る。`client_ip` は必須）
4. スコアが `SPAM_THRESHOLD` 以上なら件名に `[要確認]` を付け、全件メール送信
   - ブロックしたい場合は `submit.php` 末尾のコメントのコードに差し替える
   - API エラー・通信エラー時はスコア 0 として素通し（ブロックしたい場合はコメントのコードを使う）

## フロー A との違い

- JS 不要・実装がシンプル
- スクリプトによる直接送信をブロックできない
