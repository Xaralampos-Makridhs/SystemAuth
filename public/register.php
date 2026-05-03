<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/AuthService.php';

$database = new Database();
$conn = $database->getConnection();

$mailer = new MailService();
$auth = new AuthService($conn, $mailer);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $auth->register(
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['password'] ?? ''
    );

    $message = $ok
        ? 'Account created. Check your email to verify your account.'
        : 'Registration failed.';
}
?>

<h2>Register</h2>

<form method="POST">
    <input type="text" name="name" placeholder="Name" required>
    <br><br>

    <input type="email" name="email" placeholder="Email" required>
    <br><br>

    <input type="password" name="password" placeholder="Password" required>
    <br><br>

    <button type="submit">Register</button>
</form>

<p><?= htmlspecialchars($message) ?></p>

<a href="login.php">Login</a>
