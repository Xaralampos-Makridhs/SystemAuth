<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/AuthService.php';

$database = new Database();
$conn = $database->getConnection();

$mailer = new MailService();
$auth = new AuthService($conn, $mailer);

$token = trim($_GET['token'] ?? '');

if ($auth->verifyEmail($token)) {
    echo 'Email verified successfully. <a href="login.php">Login</a>';
} else {
    echo 'Invalid or expired verification link.';
}