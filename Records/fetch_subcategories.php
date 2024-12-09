<?php
require "../includes/db.php";

// Get the category_id from the query parameter
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id > 0) {
    // Prepare and execute the query to fetch subcategories based on the category_id
    $query = "SELECT * FROM subcategory WHERE categoryId = :category_id ORDER BY subcategory_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the results as an associative array
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the subcategories as JSON
    echo json_encode($subcategories);
} else {
    // Return an empty array if no category_id is provided or if it's invalid
    echo json_encode([]);
}
?>