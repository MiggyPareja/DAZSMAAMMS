<?php
require 'includes/db.php';
$room_type_id = intval($_GET['room_type']);
$stmt = $conn->prepare("SELECT id, name FROM rooms WHERE room_type_id = ?");
$stmt->execute([$room_type_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

