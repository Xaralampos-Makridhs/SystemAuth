<?php

class AuthService
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    private function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    private function hashToken($token)
    {
        return hash('sha256', $token);
    }

    public function register($name, $email, $password)
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $this->conn->beginTransaction();

            // Insert user
            $stmt = $this->conn->prepare("
                INSERT INTO users (name, email, password_hash)
                VALUES (:name, :email, :password_hash)
            ");

            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":password_hash" => $passwordHash
            ]);

            $userId = (int) $this->conn->lastInsertId();

            // Create verification token
            $token = $this->generateToken();
            $tokenHash = $this->hashToken($token);

            $stmt = $this->conn->prepare("
                INSERT INTO email_verified_tokens (user_id, token_hash, expires_at)
                VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ");

            $stmt->execute([
                ":user_id" => $userId,
                ":token_hash" => $tokenHash
            ]);

            $this->conn->commit();

            // προσωρινά για testing
            $_SESSION['verify_token'] = $token;

            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Register Error: " . $e->getMessage());
            return false;
        }
    }
}