<?php
// Include database connection
include '../includes/db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the user data from the form
    $id_number = $_POST['id_number']; // This should be passed with the form submission
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    $birthdate = $_POST['birthdate'];
    $profile_picture = $_FILES['profile_picture'];
    
    // Check if the username already exists (excluding the current user)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id_number != ?");
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $id_number);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "Error: The username is already taken by another user.";
    } else {
        // Handle profile picture upload
        if ($profile_picture['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($profile_picture["name"]);
            move_uploaded_file($profile_picture["tmp_name"], $target_file);
        } else {
            $target_file = null;
        }

        // Update query (with password handling)
        if (!empty($password)) {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ?, status = ?, first_name = ?, last_name = ?, contact_number = ?, birthdate = ?, profile_picture = ? WHERE id_number = ?");
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $hashed_password);
            $stmt->bindParam(4, $role);
            $stmt->bindParam(5, $status);
            $stmt->bindParam(6, $first_name);
            $stmt->bindParam(7, $last_name);
            $stmt->bindParam(8, $contact_number);
            $stmt->bindParam(9, $birthdate);
            $stmt->bindParam(10, $target_file);
            $stmt->bindParam(11, $id_number);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ?, first_name = ?, last_name = ?, contact_number = ?, birthdate = ?, profile_picture = ? WHERE id_number = ?");
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $role);
            $stmt->bindParam(4, $status);
            $stmt->bindParam(5, $first_name);
            $stmt->bindParam(6, $last_name);
            $stmt->bindParam(7, $contact_number);
            $stmt->bindParam(8, $birthdate);
            $stmt->bindParam(9, $target_file);
            $stmt->bindParam(10, $id_number);
        }
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "User updated successfully.";
            header("Location: manage_users.php");
            exit();
        } else {
            echo "Error updating user.";
        }
    }
}
?>