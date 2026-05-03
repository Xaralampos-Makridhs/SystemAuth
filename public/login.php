<?php
    require_once __DIR__.'/../Config/Database.php';
    require_once __DIR__.'/../Services/MailService.php';
    require_once __DIR__.'/../Services/AuthService.php';

    $db=new Database();
    $conn=$db->getConnection();

    $mailer=new MailService();
    $auth=new AuthService($conn,$mailer);

    $message='';

    if($_SERVER['REQUEST_METHOD']==='POST'){
        $ok=$auth->login(
            $_POST['email'] ?? '',
            $_POST['password'] ?? ''
        );

        if($ok){
            header('Location: dashboard.php');
            exit;
        }

        $message='Invalid credentials or email not verified';
    }
?>

<h2>Login</h2>
<form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <br><br>

    <input type="password" name="password" placeholder="Password">

    <button type="submit">Login</button>
</form>
