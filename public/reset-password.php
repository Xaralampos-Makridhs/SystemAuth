<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/AuthService.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$database = new Database();
$conn = $database->getConnection();

$mailer = new MailService();
$auth = new AuthService($conn, $mailer);

$token = trim($_GET['token'] ?? '');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('Invalid CSRF token');
    }

    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($auth->resetPassword($token, $password)) {
        $message = 'Password changed successfully. <a href="login.php">Login</a>';
    } else {
        $message = 'Invalid or expired reset link.';
    }

    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>

<h2>Reset Password</h2>

<form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <input type="password" name="password" placeholder="New Password" required minlength="8">
    <br><br>

    <button type="submit">Reset Password</button>
</form>

<p><?= $message ?></p>