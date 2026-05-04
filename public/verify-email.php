<?php

    // Include the Database class file
    require_once __DIR__ . '/../Config/Database.php';
    // Include the MailService class file (used for sending emails)
    require_once __DIR__ . '/../Services/MailService.php';
    // Include the AuthService class file (handles authentication logic)
    require_once __DIR__ . '/../Services/AuthService.php';

    // Create a new Database instance
    $database = new Database();
    // Get a database connection
    $conn = $database->getConnection();

    // Create a new MailService instance
    $mailer = new MailService();
    // Create a new AuthService instance with database connection and mail service
    $auth = new AuthService($conn, $mailer);

    // Get the verification token from the URL and remove extra spaces
    $token = trim($_GET['token'] ?? '');

    // Attempt to verify the user's email using the token
    if ($auth->verifyEmail($token)) {
        // Display success message with a login link
        echo 'Email verified successfully. <a href="login.php">Login</a>';
    } else {
        // Display error message if token is invalid or expired
        echo 'Invalid or expired verification link.';
    }