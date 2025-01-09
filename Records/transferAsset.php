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
    $transferToRoom = filter_var($transferToRoom, FILTER_SANITIZE_NUMBER_INT);

    try {
        // Fetch the current room ID of the asset
        $stmt = $conn->prepare("SELECT room_id FROM inventory WHERE id = :asset_id");
        $stmt->bindValue(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt->execute();
        $currentRoom = $stmt->fetch(PDO::FETCH_ASSOC)['room_id'];

        // Update the inventory table with the new user and room
        $stmt = $conn->prepare("UPDATE inventory SET deployed_to = :transferToUser, room_id = :transferToRoom WHERE id = :asset_id");
        $stmt->bindValue(':transferToUser', $transferToUser, PDO::PARAM_STR);
        $stmt->bindValue(':transferToRoom', $transferToRoom, PDO::PARAM_INT);
        $stmt->bindValue(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Insert into logs
            if (isset($_SESSION['id_number'])) {
                $logQuery = "INSERT INTO logs (log_type, performed_by, log_date) VALUES (?, ?, ?)";
                $logStmt = $conn->prepare($logQuery);
                $logStmt->execute(['Transfer Asset', $_SESSION['id_number'], date('Y-m-d H:i:s')]);

                // Insert into transfer_history
                $historyQuery = "INSERT INTO transfer_history (asset_id, from_room_id, to_room_id,updated_by) VALUES (?, ?, ?, ?)";
                $historyStmt = $conn->prepare($historyQuery);
                $historyStmt->execute([$asset_id, $currentRoom, $transferToRoom, $_SESSION['id_number']]);
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