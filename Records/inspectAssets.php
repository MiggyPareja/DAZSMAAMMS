<?php
// Include your database connection
require '../includes/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if the connection is established
        if (!$conn) {
            throw new Exception('Connection failed: ' . implode(', ', $conn->errorInfo()));
        }
         // Get session id_number
    if (!isset($_SESSION['id_number'])) {
        die('Session id_number not found.');
    }
    $sessionIdNumber = $_SESSION['id_number'];

        // Retrieve form data
        $item_id = isset($_POST['item_id']) ? trim($_POST['item_id']) : '';  // Item ID from the form
        $maintenance_type = isset($_POST['maintenance_type']) ? trim($_POST['maintenance_type']) : '';  // Maintenance type
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';  // Comment

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
            throw new Exception('Error: Invalid item ID or maintenance type.');
        }

        $updateQuery = "UPDATE inventory 
                        SET inspector_points = inspector_points + :points, 
                            comments= :comment 
                        WHERE id = :item_id";  // Use invID to find the asset
        $updateStmt = $conn->prepare($updateQuery);

        // Check for errors in the query preparation
        if (!$updateStmt) {
            throw new Exception('Query preparation failed: ' . implode(', ', $conn->errorInfo()));
        }

        // Execute the query
        $updateStmt->execute([
            ':points' => $points,
            ':comment' => $comment,
            ':item_id' => $item_id,  // Bind the item ID instead of the name
        ]);

        // Check if the update was successful
        if ($updateStmt->rowCount() > 0) {
            // Insert into logs
            $logQuery = "INSERT INTO logs (log_type, performed_by) VALUES (?, ?)";
            $logStmt = $conn->prepare($logQuery);

            // Check for errors in the query preparation
            if (!$logStmt) {
                throw new Exception('Log query preparation failed: ' . implode(', ', $conn->errorInfo()));
            }

            // Execute the log query
            $logStmt->execute(['Inspect Asset', $sessionIdNumber]);

            // Check if the log insertion was successful
            if ($logStmt->rowCount() > 0) {
                echo 'Update and log insertion successful.';
            } else {
                throw new Exception('Log insertion failed.');
            }

            // Successful update
            header('Location: deployed_assets.php');
            exit();
        } else {
            header('Location: deployed_assets.php');
            throw new Exception('Error: No rows were updated. Ensure the item ID exists in the database.');
        }

    } catch (PDOException $e) {
        // Log any PDO errors
        error_log('Database error: ' . $e->getMessage());
        die('Database error: ' . $e->getMessage());
    } catch (Exception $e) {
        // Log any general errors
        error_log('Error: ' . $e->getMessage());
        die('Error: ' . $e->getMessage());
    }
} else {
    // If accessed without submitting the form, redirect back
    header('Location: assets_table.php');
    exit();
}
?>
