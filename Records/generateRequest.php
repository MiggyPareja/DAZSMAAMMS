<?php
require "../includes/db.php";

// Fetch departments
$query = "SELECT * FROM departments ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users
$userquery = "SELECT * FROM `users` WHERE users.status = 'Active'";
$stmt = $conn->prepare($userquery);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$categoryquery = "SELECT * FROM `categories` ORDER by category_id";
$stmt = $conn->prepare($categoryquery);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch brands
$brandquery = "SELECT * FROM brands ORDER BY brand_name";
$stmt = $conn->prepare($brandquery);
$stmt->execute();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Insert into Request
$success = false; // Default value for success
$error_message = ""; // Default error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign form data to variables
    $department = isset($_POST['department']) ? (int)$_POST['department'] : null;
    $created_at = $_POST['date'];
    $requested_by = isset($_POST['requestedBy']) ? (int)$_POST['requestedBy'] : null;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $status = "Pending"; // Default status
    $unit_cost = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
    $brand = isset($_POST['brand']) ? (int)$_POST['brand'] : null;
    $model = isset($_POST['model']) ? (int)$_POST['model'] : null;
    $specs = isset($_POST['specs']) ? $_POST['specs'] : null;
    $category_id = isset($_POST['category']) ? (int)$_POST['category'] : null;
    $subcategory_id = isset($_POST['subcategory']) ? (int)$_POST['subcategory'] : null;

    // Check if required fields are set
    if (!$department || !$category_id || !$subcategory_id || !$brand || !$model) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Prepare the SQL query with category_id and subcategory_id
        $sql = "INSERT INTO inventory 
                (department_id, created_at, requested_by, quantity, status, unit_cost, brand_id, model_id, specs, category_id, subcategory_id) 
                VALUES (:department, NOW(), :requested_by, :quantity, :status, :unit_cost, :brand, :model, :specs, :category_id, :subcategory_id)";

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind parameters to the prepared statement
        $stmt->bindParam(':department', $department, PDO::PARAM_INT);
        $stmt->bindParam(':requested_by', $requested_by, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':unit_cost', $unit_cost, PDO::PARAM_STR);
        $stmt->bindParam(':brand', $brand, PDO::PARAM_INT);
        $stmt->bindParam(':model', $model, PDO::PARAM_INT);
        $stmt->bindParam(':specs', $specs, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':subcategory_id', $subcategory_id, PDO::PARAM_INT);

        // Execute the statement and check if successful
        if ($stmt->execute()) {
            $success = true; // Set success to true on successful insertion
        } else {
            $error_message = $stmt->errorInfo()[2]; // Capture error message
        }
    }
}
?>
