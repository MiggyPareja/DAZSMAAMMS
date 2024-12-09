<?php
// Include your database connection
require '../includes/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $item_id = isset($_POST['item_id']) ? trim($_POST['item_id']) : '';  // Item ID from the form
    $maintenance_type = isset($_POST['maintenance_type']) ? trim($_POST['maintenance_type']) : '';  // Maintenance type

    // Map maintenance types to negative points based on form values
    $pointsMap = [
        '0'  => 0,      // No Repair
        '-5' => -5,     // Minor Repair
        '-10' => -10,   // Major Repair
        '-15' => -15,   // For Disposal
    ];

    // Determine points based on maintenance type
    $points = isset($pointsMap[$maintenance_type]) ? $pointsMap[$maintenance_type] : null;

    // Validate form inputs
    if (empty($item_id) || $points === null) {
        die('Error: Invalid item ID or maintenance type.');
    }

    try {
        // Check if the connection is established
        if (!$conn) {
            die('Connection failed: ' . implode(', ', $conn->errorInfo()));
        }

       
        $updateQuery = "UPDATE inventory 
                        SET inspector_points = inspector_points + :points 
                        WHERE id = :item_id";  // Use invID to find the asset
        $updateStmt = $conn->prepare($updateQuery);

        // Check for errors in the query preparation
        if (!$updateStmt) {
            die('Query preparation failed: ' . implode(', ', $conn->errorInfo()));
        }

        // Execute the query
        $updateStmt->execute([
            ':points' => $points,
            ':item_id' => $item_id,  // Bind the item ID instead of the name
        ]);

        // Check if the update was successful
        if ($updateStmt->rowCount() > 0) {
            // Successful update
            header('Location: deployed_assets.php');
            exit();
        } else {
            // No rows were updated, check if the item_id exists
            die('Error: No rows were updated. Ensure the item ID exists in the database.');
        }

    } catch (PDOException $e) {
        // Log any PDO errors
        die('Database error: ' . $e->getMessage());
    }
} else {
    // If accessed without submitting the form, redirect back
    header('Location: assets_table.php');
    exit();
}
?>
