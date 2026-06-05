# フロー A — ブラウザ JS フロー

ブラウザ JS がワンタイムトークンを取得し、サーバーサイドでスパム判定するフローです。

## ファイル構成

```
flow-a/
  form.html      — 問い合わせフォーム（JS でトークン取得）
  submit.php     — フォーム受信・スパム判定・メール送信
```

## セットアップ

1. `submit.php` の設定値を編集する

```php
define('CONTAICT_SITE_KEY', 'cs_xxxxxxxxxxxx'); // サイトキー
define('MAIL_TO', 'admin@example.com');
```

2. `form.html` の `YOUR_SITE_KEY` を書き換える（または submit.php から動的に埋め込む）

## 動作の流れ

1. ユーザーがフォームを送信
2. JS が `/v1/token` にテキストを送りワンタイムトークンを取得
3. トークンをフォームに埋め込んでサーバーへ POST
4. `submit.php` がトークンを `/v1/analyze` に送りスコアを取得
5. スコアが 70 以上ならブロック、未満なら通常処理
