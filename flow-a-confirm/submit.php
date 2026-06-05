<?php
session_start();

define('MAIL_TO', 'admin@example.com');

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

// 通常処理
$subject = "【お問い合わせ】{$name} 様より";
$message = "名前: {$name}\nメール: {$email}\n\n{$body}";
mail(MAIL_TO, $subject, $message, "From: {$email}");

echo 'お問い合わせを受け付けました。';
