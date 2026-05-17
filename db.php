<?php
declare(strict_types=1);

$dbHost = getenv('MYSQLHOST');
$dbUser = getenv('MYSQLUSER');
$dbPass = getenv('MYSQLPASSWORD');
$dbName = getenv('MYSQLDATABASE');
$dbPort = (int) getenv('MYSQLPORT');

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>