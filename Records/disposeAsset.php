<?php
// dispose_asset.php

// Include database connection
include '../includes/db.php';  // Adjust this line to include your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the asset ID from the form submission
    $asset_id = $_POST['asset_id'];

    // Validate and sanitize the asset ID (although PDO prepared statements prevent SQL injection)
    $asset_id = filter_var($asset_id, FILTER_SANITIZE_NUMBER_INT);

    try {
        // Prepare SQL statement to update the asset status to 'Disposed'
        $sql = "UPDATE inventory SET status = :status, disposal_date = NOW() WHERE id = :asset_id";
        
        // Prepare the statement using the PDO connection
        $stmt = $conn->prepare($sql);
        
        // Bind the parameters
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
        
        // Set the status to 'Disposed'
        $status = 'Disposed';
        
        // Execute the statement
        if ($stmt->execute()) {
            // Redirect back to the page with the deployed assets
            header("Location: deployed_assets.php");  // Change to the correct page URL
            exit();
        } else {
            echo "Error updating record.";
        }
    } catch (PDOException $e) {
        // Handle the exception
        echo "Error: " . $e->getMessage();
    }
}
?>

?>
