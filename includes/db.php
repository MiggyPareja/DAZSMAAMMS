<?php
$host = 'localhost';
$dbname = 'school_asset';
$username = 'parsu';
$password = '6!2FZd)]1.)Jn[pZ';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
