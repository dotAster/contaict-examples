<?php
session_start();

define('MAIL_TO', 'admin@example.com');
define('SPAM_THRESHOLD', 70);

// 確認画面を通っていない（直接送信・スクリプト等）
if (empty($_SESSION['contaict_checked'])) {
    http_response_code(400);
    exit('不正なアクセスです。');
}

$name  = $_SESSION['form_name']  ?? '';
$email = $_SESSION['form_email'] ?? '';
$body  = $_SESSION['form_body']  ?? '';

// セッションをクリア（二重送信防止）
unset(
    $_SESSION['contaict_score'],
    $_SESSION['contaict_checked'],
    $_SESSION['form_name'],
    $_SESSION['form_email'],
    $_SESSION['form_body']
);

// スコアに応じて件名にラベルを付けて全件送信（推奨）
$score   = $_SESSION['contaict_score'] ?? 0;
$label   = $score >= SPAM_THRESHOLD ? '[要確認] ' : '';
$subject = $label . "【お問い合わせ】{$name} 様より";
$message = "名前: {$name}\nメール: {$email}\n\n{$body}\n\n---\nスパムスコア: {$score}/100";
mail(MAIL_TO, $subject, $message, "From: {$email}");

// ブロックする場合（confirm.php のスコアチェックと組み合わせて使う）:
// if ($score >= SPAM_THRESHOLD) {
//     http_response_code(400);
//     exit('送信できませんでした。');
// }
// $subject = "【お問い合わせ】{$name} 様より";
// $message = "名前: {$name}\nメール: {$email}\n\n{$body}";
// mail(MAIL_TO, $subject, $message, "From: {$email}");

echo 'お問い合わせを受け付けました。';
