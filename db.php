<?php
$mysql_url = getenv('MYSQL_URL');

if (!$mysql_url) {
    die("MYSQL_URL environment variable not found.");
}

$parts = parse_url($mysql_url);

$host = $parts['host'];
$user = $parts['user'];
$pass = $parts['pass'];
$db   = ltrim($parts['path'], '/');
$port = $parts['port'] ?? 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>