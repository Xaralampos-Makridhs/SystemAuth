<?php
    require_once __DIR__.'/../Config/Database.php';
    require_once __DIR__.'/../Services/AuthService.php';
    require_once __DIR__.'/../Services/MailService.php';

    $database=new Database();
    $conn=$database->getConnection();

    $mailer=new MailService();
    $auth=new AuthService($conn,$mailer);

    $token=$_GET['token'] ?? '';

    if($auth->verifyEmail($token)){
        echo "Email Verified";
    }else{
        echo "Failed";
    }

