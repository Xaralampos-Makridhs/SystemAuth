<?php

require_once __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/.env')) {
    throw new Exception('.env file not found');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}