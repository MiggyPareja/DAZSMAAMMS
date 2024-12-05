<?php
require "../includes/db.php";

if (isset($_GET['brand_id'])) {
    $brand_id = $_GET['brand_id'];

    // Fetch models for the selected brand
    $query = "SELECT * FROM models WHERE brand_id = :brand_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the results as an associative array
    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the models as JSON
    echo json_encode($models);
}
?>
