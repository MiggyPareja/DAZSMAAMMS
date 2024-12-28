<?php
// dispose_asset.php

// Include database connection
include '../includes/db.php';  // Adjust this line to include your DB connection

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the asset ID from the form submission
    $asset_id = $_POST['asset_id'];

    // Validate and sanitize the asset ID (although PDO prepared statements prevent SQL injection)
    $asset_id = filter_var($asset_id, FILTER_SANITIZE_NUMBER_INT);

    try {
        // Prepare SQL statement to update the asset status to 'Disposed'
        $sql = "UPDATE inventory SET status = :status, dispose_date = NOW() WHERE id = :asset_id";

        // Prepare the statement using the PDO connection
        $stmt = $conn->prepare($sql);

        // Set the status to 'Disposed'
        $status = 'Disposed';

        // Bind the parameters
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Insert into logs
        if (isset($_SESSION['id_number'])) {
            $logQuery = "INSERT INTO logs (log_type, performed_by, log_date) VALUES (?, ?, ?)";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->execute(['Dispose Asset', $_SESSION['id_number'], date('Y-m-d H:i:s')]);
        }

        // Redirect back to the page with the deployed assets
        header("Location: deployed_assets.php");  // Change to the correct page URL
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");  
    exit();
}
?>
