<?php
require './includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $name = htmlspecialchars($_POST['name']);

    switch ($type) {
        case 'category':
            $query = "INSERT INTO categories (name) VALUES (:name)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            break;

        case 'subcategory':
            $categoryId = htmlspecialchars($_POST['category_id']);
            $query = "INSERT INTO subcategory (name, categoryId) VALUES (:name, :categoryId)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            break;

        case 'brand':
            $query = "INSERT INTO brands (brand_name) VALUES (:name)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            break;

        case 'model':
            $brandId = htmlspecialchars($_POST['brand_id']);
            $query = "INSERT INTO models (model_name, brand_id) VALUES (:name, :brandId)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':brandId', $brandId, PDO::PARAM_INT);
            break;

        default:
            echo "<script>alert('Invalid type.'); window.location.href = 'dashboard.php';</script>";
            exit;
    }

    try {
        $stmt->execute();
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Integrity constraint violation: 1062 Duplicate entry
            echo "<script>alert('Error: Duplicate entry.'); window.location.href = 'dashboard.php';</script>";
        } else {
            echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href = 'dashboard.php';</script>";
        }
    }
}
?>
