<?php
    require_once __DIR__.'/../Config/Database.php';
    require_once __DIR__.'/../Services/MailService.php';
    require_once __DIR__.'/../Services/AuthService.php';

    $database=new Database();
    $conn=$database->getConnection();

    $mailer=new MailService();
    $auth=new AuthService($conn,$mailer);

    $token=$_GET['user_id'] ?? '';
    $message='';

    if($_SERVER['REQUEST_METHOD']==='POST'){
        $token=$_POST['token'] ?? '';
        $password=$_POST['password'] ?? '';

        if($auth->resetPassword($token,$password)){
            $message='Password changed successfully. <a href="login.php">Login</a>';
        }else{
            $message="Invalid or expired reset link";
        }
    }
?>

<h2>Reset Password</h2>

<form method="post">
    <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">

    <input type="password" name="password" placeholder="New Password" required>
    <br><br>

    <button type="submit">Reset Password</button>
</form>

<p><?=$message?></p>


