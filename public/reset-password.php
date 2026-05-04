<?php

    // Include the Database class file
    require_once __DIR__ . '/../Config/Database.php';
    // Include the MailService class file (used for sending emails)
    require_once __DIR__ . '/../Services/MailService.php';
    // Include the AuthService class file (handles authentication logic)
    require_once __DIR__ . '/../Services/AuthService.php';

    // Check if a CSRF token is not already set in the session
    if (empty($_SESSION['csrf'])) {
        // Generate a secure random CSRF token and store it in the session
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    // Create a new Database instance
    $database = new Database();
    // Get a database connection
    $conn = $database->getConnection();

    // Create a new MailService instance
    $mailer = new MailService();
    // Create a new AuthService instance with database connection and mail service
    $auth = new AuthService($conn, $mailer);
    // Get the reset token from the URL and remove extra spaces
    $token = trim($_GET['token'] ?? '');

    // Initialize a message variable for user feedback
    $message = '';

    // Check if the request method is POST (form submission)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate the CSRF token
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
            // Stop execution if CSRF token is invalid
            die('Invalid CSRF token');
        }

        // Get the reset token from the submitted form and remove extra spaces
        $token = trim($_POST['token'] ?? '');
        // Get the new password from the submitted form
        $password = $_POST['password'] ?? '';

        // Try to reset the user's password using the token and new password
        if ($auth->resetPassword($token, $password)) {
            // Set a success message with a login link
            $message = 'Password changed successfully. <a href="login.php">Login</a>';
        } else {
            // Set an error message if the reset token is invalid or expired
            $message = 'Invalid or expired reset link.';
        }
        // Regenerate a new CSRF token after form submission
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
?>

<!-- Display the reset password page title -->
    <h2>Reset Password</h2>

<!-- Create a form that sends data via POST -->
    <form method="post">
<!-- Hidden input field containing the CSRF token -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
<!-- Hidden input field containing the reset token -->
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

<!-- Input field for the new password, requiring at least 8 characters -->
        <input type="password" name="password" placeholder="New Password" required minlength="8">
        <br><br>
<!-- Submit button to reset the password -->
        <button type="submit">Reset Password</button>
    </form>

<!-- Display the feedback message -->
    <p><?= $message ?></p>