<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Services/AuthService.php';

$database = new Database();
$conn = $database->getConnection();

