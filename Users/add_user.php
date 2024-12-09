<?php
// Include the database connection file
require '../includes/db.php';

// Check if the form is submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $id_number = $_POST['id_number'];
    $department_id = $_POST['department_id'] ?? null; // Optional field, could be NULL

    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($id_number)) {
        $errors[] = "ID Number is required.";
    }
    // You can add more validation here as needed
    
    // Check for errors before proceeding with database insertion
    if (empty($errors)) {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL query to insert user into the database
        $sql = "INSERT INTO users (username, password, role, status, first_name, last_name, contact_number, email, birthdate, id_number, department_id) 
                VALUES (:username, :password, :role, :status, :first_name, :last_name, :contact_number, :email, :birthdate, :id_number, :department_id)";

        try {
            // Prepare the statement using PDO
            $stmt = $conn->prepare($sql);

            // Bind parameters to the query
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':contact_number', $contact_number);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':birthdate', $birthdate);
            $stmt->bindParam(':id_number', $id_number);
            $stmt->bindParam(':department_id', $department_id);

            // Execute the query
            if ($stmt->execute()) {
                // Redirect to the manage users page with success status
                header("Location: manage_users.php?status=success");
                exit();
            } else {
                $errors[] = "Error executing query: " . $stmt->errorInfo()[2];
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }

    // Display errors if there are any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
}

// Close the database connection
$conn = null;
?>
