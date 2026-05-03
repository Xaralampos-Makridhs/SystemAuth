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

    public function login($email,$password){
        $email=strtolower($email);
        $ip=$_SERVER['REMOTE_ADDR'] ?? 'unknown';

        try{
            $stmt=$this->conn->prepare("
                SELECT COUNT(*)
                FROM login_attempts
                WHERE email=:email,
                AND ip_address=:ip,
                AND successful=0,
                AND attempted_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE)
            ");

            $stmt->execute([
                ":email"=>$email,
                ":ip"=>$ip
                ]);

            if((int)$stmt->fetchColumn()>=5){
                return false;
            }

            //find user
            $stmt=$this->conn->prepare("SELECT * FROM users WHERE email=:email LIMIT 1");
            $stmt->execute([
                ":email"=>$email
            ]);

            $user=$stmt->fetch();

            $success=$user && password_verify($password,$user['password_hash']);

            $stmt=$this->conn->prepare("
                INSERT INTO login_attempts(email,ip_address,successful) VALUES (:email,:ip,:success)
            ");

            $stmt->execute([
               ":email"=>$email,
               ":ip"=>$ip,
               ":success"=>$success
            ]);

            if(!$success){
                return false;
            }

            if(!$user['email_verified_at']){
                return false;
            }

            session_regenerate_id(true);

            $_SESSION['user_id']=$user['id'];
            $_SESSION['user_name']=$user['name'];
            return true;
        }catch (PDOException $e){
            error_log("Login Error:").$e->getMessage();
            return false;
        }
    }

    public function sendPasswordReset($email){
        $email=strtolower($email);

        try{
            $stmt=$this->conn->prepare("
                SELECT id,name,email
                FROM users
                WHERE email=:email
                LIMIT 1
            ");

            $stmt->execute([
               ":email"=>$email
            ]);

            $user=$stmt->fetch();

            if(!$user){
                return true;
            }
            $token=$this->generateToken();
            $tokenHash=$this->hashToken($token);

            $stmt=$this->conn->prepare("
                INSERT INTO password_reset_tokens(user_id,token_hash,expires_at) VALUES (:user_id,:token_hash,DATE_ADD(NOW(),INTERVAL 30 MINUTE))
            ");

            $stmt->execute([
               ':user_id'=>$user['id'],
                ':token_hash'=>$tokenHash
            ]);

            $link=$_ENV['APP_URL'].'/reset-password.php?token='.urlencode($token);

            $html="
                <h2>Password</h2>
                <p>Hello {$user['name']}</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='{$link}'>Reset Password</a></p>
                <p>This link expires in 30 minutes</p>
            ";

            return $this->mailer->send($user['email'],'Reset your password',$html);
        }catch (PDOException $e){
            error_log("Password Reset Error: ").$e->getMessage();
            return false;
        }
    }

    public function resetPassword($token, $newPassword)
    {
        if (!$token || strlen($newPassword) < 8) {
            return false;
        }

        $tokenHash = $this->hashToken($token);

        try {
            $stmt = $this->conn->prepare("
            SELECT *
            FROM password_reset_tokens
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

            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
            UPDATE users
            SET password_hash = :password_hash
            WHERE id = :user_id
        ");

            $stmt->execute([
                ':password_hash' => $passwordHash,
                ':user_id' => $row['user_id']
            ]);

            $stmt = $this->conn->prepare("
            UPDATE password_reset_tokens
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

            error_log('Reset Password Error: ' . $e->getMessage());
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