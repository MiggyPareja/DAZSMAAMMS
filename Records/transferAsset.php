<?php
// Include database connection
include '../includes/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the asset ID, new user ID, and new room ID from the form submission
    $asset_id = $_POST['asset_id'];
    $transferToUser = $_POST['transferToUser'];
    $transferToRoom = $_POST['transferToRoom'];

    // Validate and sanitize the inputs
    $asset_id = filter_var($asset_id, FILTER_SANITIZE_NUMBER_INT);
    $transferToUser = filter_var($transferToUser, FILTER_SANITIZE_STRING);
    $transferToRoom = filter_var($transferToRoom, FILTER_SANITIZE_STRING);

    try {
        // Prepare SQL statement to update the asset's deployed_to and room_id fields
        $stmt = $conn->prepare("UPDATE inventory SET deployed_to = :transferToUser, room_id = :transferToRoom WHERE id = :asset_id");
        $stmt->bindValue(':transferToUser', $transferToUser, PDO::PARAM_STR);
        $stmt->bindValue(':transferToRoom', $transferToRoom, PDO::PARAM_STR);
        $stmt->bindValue(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Insert into transfer_logs
            $logStmt = $conn->prepare("INSERT INTO transfer_logs (asset_id, from_room_id, to_room_id, updated_by) VALUES (:asset_id, (SELECT room_id FROM inventory WHERE id = :asset_id), :transferToRoom, :updated_by)");
            $logStmt->bindValue(':asset_id', $asset_id, PDO::PARAM_INT);
            $logStmt->bindValue(':transferToRoom', $transferToRoom, PDO::PARAM_STR);
            $logStmt->bindValue(':updated_by', $_SESSION['id_number'], PDO::PARAM_INT);
            $logStmt->execute();

            // Insert into logs
            if (isset($_SESSION['id_number'])) {
                $logQuery = "INSERT INTO logs (log_type, performed_by, log_date) VALUES (?, ?, ?)";
                $logStmt = $conn->prepare($logQuery);
                $logStmt->execute(['Transfer Asset', $_SESSION['id_number'], date('Y-m-d H:i:s')]);
            }

            // Redirect back to deployed_assets.php on success
            header("Location: deployed_assets.php?success=1");
            exit();
        } else {
            // Redirect back to deployed_assets.php with an error message
            header("Location: deployed_assets.php?success=0&message=No rows updated.");
            exit();
        }

    } catch (Exception $e) {
        // Redirect back to deployed_assets.php with an error message
        header("Location: deployed_assets.php?success=0&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirect back to deployed_assets.php if the request method is not POST
    header("Location: deployed_assets.php");
    exit();
}
?>