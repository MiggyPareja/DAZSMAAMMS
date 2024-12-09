<?php
$host = 'localhost';
$dbname = 'dazmas';
$username = 'root';
$password = '';

try {
    // Initialize PDO object
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    die('Database connection failed: ' . $e->getMessage());
}
?>
