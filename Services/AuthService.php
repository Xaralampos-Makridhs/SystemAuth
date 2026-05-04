<?php

// Define the AuthService class
class AuthService
{
    // Store the database connection
    private $conn;

    // Store the mail service object
    private $mailer;

    // Constructor method that receives the database connection and mailer
    public function __construct($conn, $mailer)
    {
        // Assign the database connection to the class property
        $this->conn = $conn;

        // Assign the mailer object to the class property
        $this->mailer = $mailer;
    }

    // Generate a secure random token
    private function generateToken()
    {
        // Return a hexadecimal version of 32 random bytes
        return bin2hex(random_bytes(32));
    }

    // Hash a token before storing or comparing it
    private function hashToken($token)
    {
        // Return the SHA-256 hash of the token
        return hash('sha256', $token);
    }

    // Register a new user
    public function register($name, $email, $password)
    {
        // Remove extra spaces from the name
        $name = trim($name);

        // Convert email to lowercase and remove extra spaces
        $email = strtolower(trim($email));

        // Validate name, email, and password length
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            // Return false if validation fails
            return false;
        }

        // Hash the password using BCRYPT
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Start error handling block
        try {
            // Begin a database transaction
            $this->conn->beginTransaction();

            // Prepare SQL query to insert a new user
            $stmt = $this->conn->prepare("
                INSERT INTO users (name, email, password_hash)
                VALUES (:name, :email, :password_hash)
            ");

            // Execute the insert query with user data
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => $passwordHash
            ]);

            // Get the ID of the newly inserted user
            $userId = (int) $this->conn->lastInsertId();

            // Generate a verification token
            $token = $this->generateToken();

            // Hash the verification token
            $tokenHash = $this->hashToken($token);

            // Prepare SQL query to store the email verification token
            $stmt = $this->conn->prepare("
                INSERT INTO email_verified_tokens (user_id, token_hash, expires_at)
                VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ");

            // Execute the query and save the verification token
            $stmt->execute([
                ':user_id' => $userId,
                ':token_hash' => $tokenHash
            ]);

            // Commit the transaction
            $this->conn->commit();

            // Create the email verification link
            $link = $_ENV['APP_URL'] . '/verify-email.php?token=' . urlencode($token);

            // Create the HTML content for the verification email
            $html = "
                <h2>Verify Your Email</h2>
                <p>Hello {$name},</p>
                <p>Click the link below to verify your email.</p>
                <p><a href='{$link}'>Verify Email</a></p>
                <p>This link expires in 1 hour.</p>
            ";

            // Send the verification email and return the result
            return $this->mailer->send($email, 'Verify Your Email', $html);
        } catch (PDOException $e) {
            // Check if a transaction is still active
            if ($this->conn->inTransaction()) {
                // Roll back the transaction if an error occurred
                $this->conn->rollBack();
            }

            // Log the registration error
            error_log('Register Error: ' . $e->getMessage());

            // Return false if registration fails
            return false;
        }
    }

    // Log in an existing user
    public function login($email, $password)
    {
        // Convert email to lowercase and remove extra spaces
        $email = strtolower(trim($email));

        // Get the user's IP address, or use "unknown" if unavailable
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Start error handling block
        try {
            // Prepare SQL query to count recent failed login attempts
            $stmt = $this->conn->prepare("
                SELECT COUNT(*)
                FROM login_attempts
                WHERE email = :email
                  AND ip_address = :ip
                  AND successful = 0
                  AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ");

            // Execute the query with email and IP address
            $stmt->execute([
                ':email' => $email,
                ':ip' => $ip
            ]);

            // Check if there have been 5 or more failed attempts recently
            if ((int) $stmt->fetchColumn() >= 5) {
                // Return false to block login temporarily
                return false;
            }

            // Prepare SQL query to find the user by email
            $stmt = $this->conn->prepare("
                SELECT *
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            // Execute the query with the email
            $stmt->execute([
                ':email' => $email
            ]);

            // Fetch the user record
            $user = $stmt->fetch();

            // Verify that the user exists and the password is correct
            $success = $user && password_verify($password, $user['password_hash']);

            // Prepare SQL query to save the login attempt
            $stmt = $this->conn->prepare("
                INSERT INTO login_attempts (email, ip_address, successful)
                VALUES (:email, :ip, :success)
            ");

            // Execute the query and record whether login was successful
            $stmt->execute([
                ':email' => $email,
                ':ip' => $ip,
                ':success' => $success ? 1 : 0
            ]);

            // Check if login failed
            if (!$success) {
                // Return false if credentials are invalid
                return false;
            }

            // Check if the user's email is not verified
            if (!$user['email_verified_at']) {
                // Return false if email verification is missing
                return false;
            }

            // Regenerate the session ID for security
            session_regenerate_id(true);

            // Store the user's ID in the session
            $_SESSION['user_id'] = $user['id'];

            // Store the user's name in the session
            $_SESSION['user_name'] = $user['name'];

            // Return true if login succeeds
            return true;
        } catch (PDOException $e) {
            // Log the login error
            error_log('Login Error: ' . $e->getMessage());

            // Return false if login fails due to an error
            return false;
        }
    }

    // Verify a user's email address
    public function verifyEmail($token)
    {
        // Check if token is empty
        if (!$token) {
            // Return false if no token is provided
            return false;
        }

        // Hash the provided token
        $tokenHash = $this->hashToken($token);

        // Start error handling block
        try {
            // Prepare SQL query to find a valid email verification token
            $stmt = $this->conn->prepare("
                SELECT *
                FROM email_verified_tokens
                WHERE token_hash = :token_hash
                  AND used_at IS NULL
                  AND expires_at > NOW()
                LIMIT 1
            ");

            // Execute the query with the hashed token
            $stmt->execute([
                ':token_hash' => $tokenHash
            ]);

            // Fetch the token record
            $row = $stmt->fetch();

            // Check if no valid token was found
            if (!$row) {
                // Return false if token is invalid or expired
                return false;
            }

            // Begin a database transaction
            $this->conn->beginTransaction();

            // Prepare SQL query to mark the user's email as verified
            $stmt = $this->conn->prepare("
                UPDATE users
                SET email_verified_at = NOW()
                WHERE id = :user_id
            ");

            // Execute the query using the user ID from the token record
            $stmt->execute([
                ':user_id' => $row['user_id']
            ]);

            // Prepare SQL query to mark the verification token as used
            $stmt = $this->conn->prepare("
                UPDATE email_verified_tokens
                SET used_at = NOW()
                WHERE id = :id
            ");

            // Execute the query using the token record ID
            $stmt->execute([
                ':id' => $row['id']
            ]);

            // Commit the transaction
            $this->conn->commit();

            // Return true if verification succeeds
            return true;
        } catch (PDOException $e) {
            // Check if a transaction is still active
            if ($this->conn->inTransaction()) {
                // Roll back the transaction if an error occurred
                $this->conn->rollBack();
            }

            // Log the email verification error
            error_log('Verify Email Error: ' . $e->getMessage());

            // Return false if verification fails
            return false;
        }
    }

    // Send a password reset email
    public function sendPasswordReset($email)
    {
        // Convert email to lowercase and remove extra spaces
        $email = strtolower(trim($email));

        // Start error handling block
        try {
            // Prepare SQL query to find a user by email
            $stmt = $this->conn->prepare("
                SELECT id, name, email
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            // Execute the query with the email
            $stmt->execute([
                ':email' => $email
            ]);

            // Fetch the user record
            $user = $stmt->fetch();

            // Check if user does not exist
            if (!$user) {
                // Return true to avoid revealing whether the email exists
                return true;
            }

            // Generate a password reset token
            $token = $this->generateToken();

            // Hash the password reset token
            $tokenHash = $this->hashToken($token);

            // Prepare SQL query to store the password reset token
            $stmt = $this->conn->prepare("
                INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
                VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE))
            ");

            // Execute the query and save the password reset token
            $stmt->execute([
                ':user_id' => $user['id'],
                ':token_hash' => $tokenHash
            ]);

            // Create the password reset link
            $link = $_ENV['APP_URL'] . '/reset-password.php?token=' . urlencode($token);

            // Create the HTML content for the password reset email
            $html = "
                <h2>Password Reset</h2>
                <p>Hello {$user['name']},</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='{$link}'>Reset Password</a></p>
                <p>This link expires in 30 minutes.</p>
            ";

            // Send the password reset email and return the result
            return $this->mailer->send($user['email'], 'Reset your password', $html);
        } catch (PDOException $e) {
            // Log the password reset error
            error_log('Password Reset Error: ' . $e->getMessage());

            // Return false if sending the reset email fails
            return false;
        }
    }

    // Reset the user's password using a valid token
    public function resetPassword($token, $newPassword)
    {
        // Check if token is empty or password is shorter than 8 characters
        if (!$token || strlen($newPassword) < 8) {
            // Return false if validation fails
            return false;
        }

        // Hash the provided reset token
        $tokenHash = $this->hashToken($token);

        // Start error handling block
        try {
            // Prepare SQL query to find a valid password reset token
            $stmt = $this->conn->prepare("
                SELECT *
                FROM password_reset_tokens
                WHERE token_hash = :token_hash
                  AND used_at IS NULL
                  AND expires_at > NOW()
                LIMIT 1
            ");

            // Execute the query with the hashed token
            $stmt->execute([
                ':token_hash' => $tokenHash
            ]);

            // Fetch the token record
            $row = $stmt->fetch();

            // Check if no valid token was found
            if (!$row) {
                // Return false if token is invalid or expired
                return false;
            }

            // Hash the new password using BCRYPT
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

            // Begin a database transaction
            $this->conn->beginTransaction();

            // Prepare SQL query to update the user's password
            $stmt = $this->conn->prepare("
                UPDATE users
                SET password_hash = :password_hash
                WHERE id = :user_id
            ");

            // Execute the query using the new password hash and user ID
            $stmt->execute([
                ':password_hash' => $passwordHash,
                ':user_id' => $row['user_id']
            ]);

            // Prepare SQL query to mark the password reset token as used
            $stmt = $this->conn->prepare("
                UPDATE password_reset_tokens
                SET used_at = NOW()
                WHERE id = :id
            ");

            // Execute the query using the token record ID
            $stmt->execute([
                ':id' => $row['id']
            ]);

            // Commit the transaction
            $this->conn->commit();

            // Return true if password reset succeeds
            return true;
        } catch (PDOException $e) {
            // Check if a transaction is still active
            if ($this->conn->inTransaction()) {
                // Roll back the transaction if an error occurred
                $this->conn->rollBack();
            }

            // Log the password reset error
            error_log('Reset Password Error: ' . $e->getMessage());

            // Return false if password reset fails
            return false;
        }
    }
}