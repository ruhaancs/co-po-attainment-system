<?php
declare(strict_types=1);

$dbHost = "127.0.0.1";
$dbUser = "root";
$dbPass = "";
$dbName = "co_po_system";

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
