<?php
// Include database connection file
include_once '../includes/db.php';

// Check if the user ID is set in the URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prepare the SQL delete statement
    $sql = "DELETE FROM users WHERE id_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to manage_users.php after successful deletion
        header("Location: manage_users.php");
        exit();
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Error deleting user: " . $errorInfo[2];
    }

    // Close the statement and connection
    $stmt = null;
    $conn = null;
} else {
    echo "No user ID provided.";
}
?>