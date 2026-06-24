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
        $err = json_decode($raw, true)['error'] ?? '';
        if ($err === 'rescan_limit_exceeded') {
            http_response_code(400);
            exit('お手数ですが、ページを更新してもう一度送信してください。');
        }
        http_response_code(429);
        exit('しばらく時間をおいて再度お試しください。');
    default:
        http_response_code(503);
        exit('一時的にご利用できません。しばらく時間をおいて再度お試しください。');
}

$result = json_decode($raw, true);
$score  = $result['score'];

// スコアに応じて件名にラベルを付けて全件送信（推奨）
// ブロックする場合は「ブロックする場合」のコードに差し替える
$label   = $score >= SPAM_THRESHOLD ? '[要確認] ' : '';
$subject = $label . "【お問い合わせ】{$name} 様より";
$message = "名前: {$name}\nメール: {$email}\n\n{$body}\n\n---\nスパムスコア: {$score}/100";
mail(MAIL_TO, $subject, $message, "From: {$email}");

// ブロックする場合:
// if ($score >= SPAM_THRESHOLD) {
//     http_response_code(400);
//     exit('送信できませんでした。');
// }
// $subject = "【お問い合わせ】{$name} 様より";
// $message = "名前: {$name}\nメール: {$email}\n\n{$body}";
// mail(MAIL_TO, $subject, $message, "From: {$email}");

echo 'お問い合わせを受け付けました。';
