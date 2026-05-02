<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__. '/../Services/MailService.php';

$database = new Database();
$conn = $database->getConnection();

$mailer=new MailService();
$auth=new AuthService($conn,$mailer);

$result=$auth->register(
    'Test User',
    'makridhs.xaralampos@gmail.com',
    'password123'
);

var_dump($result);
