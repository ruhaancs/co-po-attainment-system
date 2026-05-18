<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqlUrl = getenv('MYSQL_URL');

if (!$mysqlUrl) {
    die('MYSQL_URL environment variable not found.');
}

$parts = parse_url($mysqlUrl);

if ($parts === false) {
    die('Failed to parse MYSQL_URL.');
}

$host = $parts['host'] ?? '';
$port = $parts['port'] ?? 3306;
$user = $parts['user'] ?? '';
$pass = $parts['pass'] ?? '';
$dbname = ltrim($parts['path'] ?? '', '/');

if (!$host || !$user || !$dbname) {
    die('Invalid MYSQL_URL format.');
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}