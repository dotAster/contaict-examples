# ContAIct Examples

[ContAIct](https://contaict.app) API の組み込みサンプルです。

## サンプル一覧

| ディレクトリ | 説明 |
|---|---|
| [flow-a/](./flow-a/) | ブラウザ JS フロー（サイトキー + PHP） |
| [flow-a-confirm/](./flow-a-confirm/) | ブラウザ JS フロー・確認画面あり版 |
| [flow-b/](./flow-b/) | Bearer フロー（サーバーキー + PHP） |

## フローの選び方

- **フロー A（推奨）**：ブラウザ JS がワンタイムトークンを取得し、サーバーサイドでスパム判定。スクリプトによる直接送信をブロックできます。
- **フロー B**：サーバーサイドのみで完結。実装がシンプルです。

詳しくは [使い方](https://contaict.app/usage) / [API 仕様](https://contaict.app/spec) をご覧ください。

## 必要なもの

- ContAIct アカウント（[登録はこちら](https://my.contaict.app/register)）
- PHP 8.1 以上
- curl 拡張
