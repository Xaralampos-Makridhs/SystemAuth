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

    $ok = $auth->register(
            trim($_POST['name'] ?? ''),
            trim($_POST['email'] ?? ''),
            $_POST['password'] ?? ''
    );

    $message = $ok
            ? 'Account created. Check your email to verify your account.'
            : 'Registration failed. Email may already exist or data is invalid.';

    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>

<h2>Register</h2>

<form method="POST">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

    <input type="text" name="name" placeholder="Name" required>
    <br><br>

    <input type="email" name="email" placeholder="Email" required>
    <br><br>

    <input type="password" name="password" placeholder="Password" required minlength="8">
    <br><br>

    <button type="submit">Register</button>
</form>

<p><?= htmlspecialchars($message) ?></p>

<a href="login.php">Login</a>