<?php
// Include database connection
include '../includes/db.php';

// Check if user ID is set in the URL
if (isset($_GET['id'])) {
    // Get the user ID from the URL
    $id_number = $_GET['id'];

    // Update the user's status to "Active"
    $sql = "UPDATE users SET status = 'Active' WHERE id_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $id_number);

    // Execute the statement and check for errors
    try {
        if ($stmt->execute()) {
            // Redirect to manage_users.php after successful update
            header("Location: manage_users.php");
            exit();
        } else {
            echo "Error reactivating user.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No user ID provided.";
}
?>