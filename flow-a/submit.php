<?php
define('MAIL_TO', 'admin@example.com');
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

// スパム判定
$ch = curl_init('https://api.contaict.app/v1/analyze');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['token' => $token]),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 15,
]);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);

// トークン無効・期限切れ（10分超過）・解析済み
if (!isset($result['score'])) {
    http_response_code(400);
    exit('セッションが無効です。もう一度フォームを入力してください。');
}

if ($result['score'] >= SPAM_THRESHOLD) {
    http_response_code(400);
    exit('送信できませんでした。');
}

// 通常処理
$subject = "【お問い合わせ】{$name} 様より";
$message = "名前: {$name}\nメール: {$email}\n\n{$body}";
mail(MAIL_TO, $subject, $message, "From: {$email}");

echo 'お問い合わせを受け付けました。';
