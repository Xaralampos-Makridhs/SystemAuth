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
            //Stop execution if CSRF token is invalid
            die('Invalid CSRF token');
        }

        //Call the method to send a password reset email
        $auth->sendPasswordReset(trim($_POST['email'] ?? ''));
        //Set a generic success message(does not reveal if the email exists for security)
        $message = 'If this email exists, a reset link has been sent.';

        //Regenerate a new CSRF token after form submission
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>

<!--Display the page title-->
    <h2>Forgot Password</h2>
<!--Create a form taht sends data via POST method-->
    <form method="POST">
<!--Hidden input field containing the CSRF token(for safety)-->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
<!--Input field for the user's email-->
        <input type="email" name="email" placeholder="Email" required>
        <br><br>
<!--Submit button to send the reset link-->
        <button type="submit">Send reset link</button>
    </form>
<!--Display the message (escaped to prevent XSS)-->
    <p><?= htmlspecialchars($message) ?></p>
<!--Link to navigate back to the login page-->
    <a href="login.php">Back to login</a>