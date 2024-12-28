<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

require '../includes/db.php';
require '../includes/phpqrcode/phpqrcode.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form data
    $requestId = $_POST['requestId'];
    $roomId = $_POST['deployToRoom'];
    $deployedToUser = $_POST['deployToUser']; // The user to which the asset is deployed
    $comments = $_POST['comments'];

    // Get session id_number
    if (!isset($_SESSION['id_number'])) {
        die('Session id_number not found.');
    }
    $sessionIdNumber = $_SESSION['id_number'];

    // Debugging: Check if the deployed_to value is coming through
    var_dump($deployedToUser); // Output deployed_to for debugging

    // Retrieve asset from the database using the requestId
    $query = "SELECT * FROM inventory
    JOIN models on models.model_id = inventory.model_id
    JOIN brands on brands.brand_id = inventory.brand_id
    JOIN users on users.user_id = inventory.requested_by
    WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$requestId]);
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$asset) {
        die('Asset not found.');
    }
    $userid = $asset['deployed_to'].$asset['id'];
    $brandName = $asset['brand_name']; 
    $modelName = $asset['model_name']; 
    $deployedTo = $asset['deployed_to']; // This is the previously stored value
    $lastInspected = $asset['last_inspected'];

    // Get current date and time
    $currentDateTime = date('Y-m-d'); // Format: Year-Month-Day Hour:Minute:Second

    // Generate unique name in the required format: brand_name + model_name + date/time
    $uniqueName = $deployedToUser.'-'.$brandName . '- ' . $currentDateTime;

    // Update the inventory table first with the data
    $updateQuery = "UPDATE inventory 
                    SET name = ?, deployed_to = ?, room_id = ?, comments = ?, last_inspected = ?, status = ?
                    WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([$uniqueName, $deployedToUser, $roomId, $comments, $currentDateTime, 'deployed', $requestId]);

    // Insert into logs
    $logQuery = "INSERT INTO logs (log_type, performed_by, log_date) VALUES (?, ?, ?)";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->execute(['Deploy Asset', $sessionIdNumber, $currentDateTime]);

    // Redirect or perform other actions as needed
    header('Location: add_assets.php');
    exit();
}
?>