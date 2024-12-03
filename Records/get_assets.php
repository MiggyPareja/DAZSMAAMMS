<?php
require 'includes/db.php';

$category_id = intval($_GET['category']);
$sub_category_id = intval($_GET['sub_category']);

try {

    $stmt = $conn->prepare("
        SELECT id, name 
        FROM assets 
        WHERE category_id = :category_id 
        AND sub_category_id = :sub_category_id
    ");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':sub_category_id', $sub_category_id, PDO::PARAM_INT);
    $stmt->execute();

 
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode($assets);
} catch (PDOException $e) {

    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>