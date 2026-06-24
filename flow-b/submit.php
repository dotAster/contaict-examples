<?php
define('CONTAICT_SECRET_KEY', 'sk_xxxxxxxxxxxx');
define('MAIL_TO', 'admin@example.com');
define('SPAM_THRESHOLD', 70);

$name  = trim($_POST['name']  ?? '');
$email = trim($_POST['email'] ?? '');
$body  = trim($_POST['body']  ?? '');

if (!$name || !$email || !$body) {
    http_response_code(400);
    exit('入力内容をご確認ください。');
}

// スパム判定
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
$ch = curl_init('https://api.contaict.app/v1/analyze');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['text' => $body, 'client_ip' => $clientIp]),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . CONTAICT_SECRET_KEY,
    ],
    CURLOPT_TIMEOUT        => 15,
]);
$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_errno($ch);
curl_close($ch);

$score = 0;

if (!$curlErr && $httpCode === 200) {
    $score = (int)(json_decode($raw, true)['score'] ?? 0);
} elseif ($httpCode === 429) {
    // rate_limit_exceeded または quota_exceeded
    // 月次上限超過時にブロックしたい場合:
    //   http_response_code(503); exit('一時的にご利用できません。しばらく時間をおいて再度お試しください。');
    $score = 0;
} elseif ($httpCode === 401 || $httpCode === 403) {
    // APIキー設定ミスまたはIP制限 → サーバーログで確認を
    error_log("[ContAIct] 認証エラー: HTTP {$httpCode}");
    $score = 0;
} else {
    // 通信エラー・タイムアウト・その他 → 素通し（フォールバック）
    // ブロックしたい場合:
    //   http_response_code(503); exit('一時的にご利用できません。しばらく時間をおいて再度お試しください。');
    $score = 0;
}

// スコアに応じて件名にラベルを付けて全件送信（推奨）
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
