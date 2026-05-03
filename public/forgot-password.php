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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('Invalid CSRF token');
    }

    $auth->sendPasswordReset(trim($_POST['email'] ?? ''));

    $message = 'If this email exists, a reset link has been sent.';

    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>

<h2>Forgot Password</h2>

<form method="POST">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

    <input type="email" name="email" placeholder="Email" required>
    <br><br>

    <button type="submit">Send reset link</button>
</form>

<p><?= htmlspecialchars($message) ?></p>

<a href="login.php">Back to login</a>