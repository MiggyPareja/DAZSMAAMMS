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
        $department = $_POST['department'];
        $request_date = $_POST['date'];
        $person_in_charge_id = isset($_POST['requestedBy']) ? (int)$_POST['requestedBy'] : null;
        $quantity = (int)$_POST['quantity'];
        $status = "Pending"; // Default status
        $unit_cost = isset($_POST['price']) ? (float)$_POST['price'] : null;
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $specs = $_POST['specs'];

        // Prepare the SQL query
        $sql = "INSERT INTO procurement_requests (department, request_date, person_in_charge_id, quantity, status, unit_cost, brand, model, specs) 
                VALUES (:department, :request_date, :person_in_charge_id, :quantity, :status, :unit_cost, :brand, :model, :specs)";

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind parameters to the prepared statement
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':request_date', $request_date);
        $stmt->bindParam(':person_in_charge_id', $person_in_charge_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':unit_cost', $unit_cost, PDO::PARAM_STR);
        $stmt->bindParam(':brand', $brand, PDO::PARAM_STR);
        $stmt->bindParam(':model', $model, PDO::PARAM_STR);
        $stmt->bindParam(':specs', $specs, PDO::PARAM_STR);

        // Execute the statement and check if successful
        if ($stmt->execute()) {
            $success = true; // Set success to true on successful insertion
       
        } else {
            $error_message = $stmt->errorInfo()[2]; // Capture error message
        
        }
    }
?>
