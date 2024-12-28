<?php
require '../includes/db.php';

try {
    $updateDurabilityQuery = "
        UPDATE inventory
        SET durability = (DATEDIFF(COALESCE(dispose_date, NOW()), created_at) + inspector_points) 
            - (YEAR(COALESCE(dispose_date, NOW())) - YEAR(created_at))
    ";
    $stmt = $conn->prepare($updateDurabilityQuery);
    $stmt->execute();

    header("Location: ../dashboard.php");
    exit();
} catch (PDOException $e) {
    error_log("Error updating durability: " . $e->getMessage());
    header("Location: ../dashboard.php?");
    exit();
}
?>
