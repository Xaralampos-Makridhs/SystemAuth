<?php

require_once __DIR__ . '/../bootstrap.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<h2>Dashboard</h2>

<p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p>

<a href="logout.php">Logout</a>