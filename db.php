<?php
declare(strict_types=1);

$dbHost = mysql.railway.internal;
$dbUser = root;
$dbPass = vijkiUKmRcXwvilSWRJkocJLtRFkTdUI;
$dbName = railway;
$dbPort = (int)3306;

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>