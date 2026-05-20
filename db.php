<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$dbname = getenv('MYSQLDATABASE');

if (!$host || !$user || !$dbname) {
    die('Database environment variables are missing.');
}

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>