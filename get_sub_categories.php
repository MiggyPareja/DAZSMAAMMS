<?php
require 'includes/db.php';

$category_id = intval($_GET['category']);

try {
    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT id, name FROM sub_categories WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all matching subcategories
    $sub_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    echo json_encode($sub_categories);
} catch (PDOException $e) {
    // Handle any errors by returning an error message
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>