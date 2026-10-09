# contAIct Examples

[contAIct](https://contaict.app)（AI によるお問い合わせフォームのスパム判定 API）の組み込みサンプルです。

## Features

- ブラウザ JS フロー（サイトキー）とサーバー間の Bearer フロー（サーバーキー）の両方を収録
- 確認画面のないフォームと、確認画面のあるフォームの2パターン
- スパムを破棄せず、判定結果のラベルを付けて全件送信する既定動作
- PHP と curl 拡張だけで動作（外部ライブラリ不要）

## Requirements

- contAIct アカウント（[登録はこちら](https://my.contaict.app/register)）
- PHP 8.1 以上
- curl 拡張

## Installation

```bash
git clone https://github.com/dotAster/contaict-examples.git
```

使うフローのディレクトリを Web サーバーに置き、各ディレクトリの README に従って設定値を書き換えてください。

## Usage

| ディレクトリ | 説明 |
|---|---|
| [flow-a/](./flow-a/) | ブラウザ JS フロー（サイトキー + PHP） |
| [flow-a-confirm/](./flow-a-confirm/) | ブラウザ JS フロー・確認画面あり版 |
| [flow-b/](./flow-b/) | Bearer フロー（サーバーキー + PHP） |

- **フロー A（推奨）**：ブラウザ JS がトークンを取得し、サーバーサイドでスパム判定します。スクリプトによる直接送信をブロックできます。
- **フロー B**：サーバーサイドのみで完結します。実装がシンプルです。

詳しくは [使い方](https://contaict.app/usage) / [API 仕様](https://contaict.app/spec) をご覧ください。

## License

LICENSE を参照
