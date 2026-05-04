<?php
    //Include the bootstrap file(initializing environment variables)
    require_once __DIR__.'/../bootstrap.php';

    //Clear all SESSION variables by assigning an empty array
    $_SESSION=[];
    //Destroy the current SESSION completely
    session_destroy();

    //Redirect the user using the login page
    header('Location: login.php');
    exit;


