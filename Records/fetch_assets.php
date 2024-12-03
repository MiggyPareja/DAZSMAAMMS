<?php
 include 'includes/db.php';

$query = $conn->query('SELECT name, assetCount FROM assets');
$assets = $query->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($assets);
?>
