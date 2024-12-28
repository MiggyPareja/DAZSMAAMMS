<?php
require '../includes/db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

$logType = $data['logType'];
$performedBy = $_SESSION['id_number'];

$log_sql = "INSERT INTO logs (log_type, performed_by, log_date) VALUES (:log_type, :performed_by, NOW())";
$log_stmt = $conn->prepare($log_sql);
$log_stmt->bindParam(':log_type', $logType, PDO::PARAM_STR);
$log_stmt->bindParam(':performed_by', $performedBy, PDO::PARAM_STR);

if ($log_stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to log action']);
}
?>
