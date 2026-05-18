<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqlUrl = getenv('MYSQL_URL');

if (!$mysqlUrl) {
    die("MYSQL_URL environment variable not found.");
}

$parts = parse_url($mysqlUrl);

if ($parts === false) {
    die("Failed to parse MYSQL_URL.");
}

$host = $parts['host'] ?? '';
$user = $parts['user'] ?? '';
$pass = $parts['pass'] ?? '';
$db   = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
$port = $parts['port'] ?? 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>