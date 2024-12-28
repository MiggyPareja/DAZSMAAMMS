<?php
// Include database connection
include '../includes/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the asset ID from the form submission
    $asset_id = $_POST['asset_id'];

    // Validate and sanitize the asset ID
    $asset_id = filter_var($asset_id, FILTER_SANITIZE_NUMBER_INT);

    try {
        // Prepare SQL statement to update the asset status to 'Deployed'
        $stmt = $conn->prepare("UPDATE inventory SET status = 'Deployed' WHERE id = :asset_id");
        $stmt->bindValue(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Insert into logs
            if (isset($_SESSION['id_number'])) {
                $logQuery = "INSERT INTO logs (log_type, performed_by, log_date) VALUES (?, ?, ?)";
                $logStmt = $conn->prepare($logQuery);
                $logStmt->execute(['Redeploy Asset', $_SESSION['id_number'], date('Y-m-d H:i:s')]);
            }

            // Redirect back to disposedAssets.php on success
            header("Location: disposedAssets.php?success=1");
            exit();
        } else {
            // Redirect back to disposedAssets.php with an error message
            header("Location: disposedAssets.php?success=0&message=No rows updated.");
            exit();
        }

    } catch (Exception $e) {
        // Redirect back to disposedAssets.php with an error message
        header("Location: disposedAssets.php?success=0&message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>