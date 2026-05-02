<?php

class AuthService
{
    private $conn;
    private $mailer;

    public function __construct($conn,$mailer){
        $this->conn = $conn;
        $this->mailer=$mailer;
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

            $link=$_ENV['APP_URL'].'/verify-email.php?token='.urlencode($token);

            $html="
                <h2>Verify Your Email </h2>
                <p>Hello {$name}!</p>
                <p>Click the link below to verify your email.</p>
                <p><a href='{$link}'>Verify Email</a></p>
                <p>This link expires in 1 hour.</p>
            ";

            return $this->mailer->send($email,'Verify Your Email',$html);

        } catch (PDOException $e) {
            if($this->conn->inTransaction()){
                $this->conn->rollBack();
            }

            error_log("Register Error: " . $e->getMessage());
            return false;
        }
    }

    public function verifyEmail($token)
    {
        if (!$token) {
            return false;
        }

        $tokenHash = $this->hashToken($token);

        try {
            $stmt = $this->conn->prepare("
            SELECT *
            FROM email_verified_tokens
            WHERE token_hash = :token_hash
              AND used_at IS NULL
              AND expires_at > NOW()
            LIMIT 1
        ");

            $stmt->execute([
                ':token_hash' => $tokenHash
            ]);

            $row = $stmt->fetch();

            if (!$row) {
                return false;
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
            UPDATE users
            SET email_verified_at = NOW()
            WHERE id = :user_id
        ");

            $stmt->execute([
                ':user_id' => $row['user_id']
            ]);

            $stmt = $this->conn->prepare("
            UPDATE email_verified_tokens
            SET used_at = NOW()
            WHERE id = :id
        ");

            $stmt->execute([
                ':id' => $row['id']
            ]);

            $this->conn->commit();

            return true;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log('Verify Email Error: ' . $e->getMessage());
            return false;
        }
    }

}