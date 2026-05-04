<?php
    //Include the bootstrap file(initializing environment variables)
    require_once __DIR__ . '/../bootstrap.php';

    //Check if the user is not logged in (no user_id stored in SESSION)
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
    exit;
}
?>

<!--Display a heading for the dashboard page-->
    <h2>Dashboard</h2>
<!--Show a welcome message with the user's name(escaped for security to prevent XSS)-->
    <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
<!--Provide a link for the user logout-->
    <a href="logout.php">Logout</a>