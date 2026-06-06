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
$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_errno($ch);
curl_close($ch);

if ($curlErr || $httpCode === 0) {
    http_response_code(503);
    exit('一時的にご利用できません。しばらく時間をおいて再度お試しください。');
}

switch ($httpCode) {
    case 200:
        break;
    case 404:
        http_response_code(400);
        exit('不正なアクセスです。');
    case 410:
        http_response_code(400);
        exit('セッションが切れました。もう一度フォームを入力してください。');
    case 429:
        http_response_code(429);
        exit('しばらく時間をおいて再度お試しください。');
    default:
        http_response_code(503);
        exit('一時的にご利用できません。しばらく時間をおいて再度お試しください。');
}

$result = json_decode($raw, true);

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
