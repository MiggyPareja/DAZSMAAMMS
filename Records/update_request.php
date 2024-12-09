<?php
require __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

try {
    // Decode JSON request
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['requestId'], $input['newStatus'])) {
        throw new Exception('Invalid input data.');
    }

    $requestId = $input['requestId'];
    $newStatus = $input['newStatus'];

    // Validate status
    $validStatuses = ['Approved', 'Declined'];
    if (!in_array($newStatus, $validStatuses, true)) {
        throw new Exception('Invalid status value.');
    }

    // Update the status in the database
    $stmt = $conn->prepare("UPDATE inventory SET status = :newStatus WHERE id = :requestId");
    $stmt->execute([
        ':newStatus' => $newStatus,
        ':requestId' => $requestId,
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request.']);
}
