<?php
include '../includes/db.php'; // Adjust as needed for your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['requestId'];
    $assetName = $_POST['assetName'];
    $roomId = $_POST['roomId'];
    $personInChargeId = $_POST['personInChargeId'];

    // Fetch necessary details for insertion
    $query = "SELECT brand_id, model_id, specs, unit_cost FROM procurement_requests WHERE procurement_request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // Insert into assets table
        $insertQuery = "
            INSERT INTO assets (name, room_id, person_in_charge_id, asset_count, brand_id, model_id, specs, status, last_updated_by)
            VALUES (?, ?, ?, 1, ?, ?, ?, 'Deployed', ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param(
            "siisssi",
            $assetName,
            $roomId,
            $personInChargeId,
            $data['brand_id'],
            $data['model_id'],
            $data['specs'],
            $userId // Replace with current user's ID
        );

        if ($insertStmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to deploy asset.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid procurement request ID.']);
    }
}
?>
