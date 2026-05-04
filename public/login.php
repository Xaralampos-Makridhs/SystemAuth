<?php

    //Include the database file
    require_once __DIR__ . '/../Config/Database.php';
    //Include the MailService class file(used for sending emails)
    require_once __DIR__ . '/../Services/MailService.php';
    //Include the AuthService class file(handles authentication-related logic)
    require_once __DIR__ . '/../Services/AuthService.php';

    //Check if a CSRF token is not already in the SESSION
    if (empty($_SESSION['csrf'])) {
        //Generate a secure random CSRF token and store it in the SESSION
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    //Create a new Database instance
    $database = new Database();
    //Get a database connection
    $conn = $database->getConnection();

    //Create a new MailService instance
    $mailer = new MailService();
    //Create a new AuthService instance with database connection and mail service
    $auth = new AuthService($conn, $mailer);

    //Initialize a message variable(user feedback)
    $message = '';

    //Check if the request method is POST(form submission)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Validate the CSRF token (compare session token with submitted token)
        if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
            //Stop execution if CSRF toke is invalid
            die('Invalid CSRF token');
        }

        //Attempt to log in the user using provided email and password
        $ok = $auth->login(
                trim($_POST['email'] ?? ''), //Get and trim email input
                $_POST['password'] ?? '' //Get password input
        );

        //If login successful
        if ($ok) {
            //Redirect the user to the dashboard page
            header('Location: dashboard.php');
            exit;
        }

        //Set an error message if login fails
        $message = 'Invalid credentials or email not verified.';

        //Regenerate a new CSRF token after submission
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
?>

<!-- Display the login page title -->
    <h2>Login</h2>

<!-- Create a login form that sends data via POST -->
    <form method="post">
<!-- Hidden input field containing the CSRF token (escaped for security) -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
<!-- Input field for the user's email -->
        <input type="email" name="email" placeholder="Email" required>
        <br><br>
<!-- Input field for the user's password -->
        <input type="password" name="password" placeholder="Password" required>
        <br><br>
<!-- Submit button to log in -->
        <button type="submit">Login</button>
    </form>

<!-- Link to the password recovery page -->
    <a href="forgot-password.php">Forgot your password?</a>
<!-- Display the message (escaped to prevent XSS) -->
    <p><?= htmlspecialchars($message) ?></p>