<?php
session_start();

define('SPAM_THRESHOLD', 70);

$token = $_POST['contaict_token'] ?? '';
$name  = trim($_POST['name']  ?? '');
$email = trim($_POST['email'] ?? '');
$body  = trim($_POST['body']  ?? '');

// トークンなし = 正規フロー（JS）を通っていない
if (!$token || !$name || !$email || !$body) {
    http_response_code(400);
    exit('不正なアクセスです。');
}

// スパム判定（確認画面でのみ実行）
$ch = curl_init('https://api.contaict.app/v1/analyze');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['token' => $token, 'text' => $body]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 15,
]);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);

// トークン無効・期限切れ（10分超過）
if (!isset($result['score'])) {
    http_response_code(400);
    exit('セッションが無効です。もう一度フォームを入力してください。');
}

if ($result['score'] >= SPAM_THRESHOLD) {
    http_response_code(400);
    exit('送信できませんでした。');
}

// スコアをセッションに保存（完了処理で再スキャンしないため）
$_SESSION['contaict_score']   = $result['score'];
$_SESSION['contaict_checked'] = true;
$_SESSION['form_name']        = $name;
$_SESSION['form_email']       = $email;
$_SESSION['form_body']        = $body;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>お問い合わせ内容の確認</title>
</head>
<body>

<h1>内容をご確認ください</h1>

<dl>
  <dt>お名前</dt>
  <dd><?= htmlspecialchars($name) ?></dd>
  <dt>メールアドレス</dt>
  <dd><?= htmlspecialchars($email) ?></dd>
  <dt>お問い合わせ内容</dt>
  <dd><?= nl2br(htmlspecialchars($body)) ?></dd>
</dl>

<form action="submit.php" method="post">
  <button type="submit">この内容で送信する</button>
</form>
<p><a href="form.html">修正する</a></p>

</body>
</html>
