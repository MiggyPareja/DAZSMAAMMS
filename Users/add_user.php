<?php

require '../includes/db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $id_number = trim($_POST['id_number']);
    $user_username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $contact_number = trim($_POST['contact_number']);
    $birthdate = $_POST['birthdate'];

    $errors = []; // Initialize an array to store error messages

    // Validate ID Number
    if (empty($id_number)) {
        $errors[] = 'ID Number is required.';
    }

    // Validate Username
    if (empty($user_username)) {
        $errors[] = 'Username is required.';
    }

    // Validate Email
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Validate Password
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    // Validate Contact Number (Optional, but you can apply a regex if needed)
    if (!empty($contact_number) && !preg_match('/^\+?[0-9]{10,15}$/', $contact_number)) {
        $errors[] = 'Invalid contact number format.';
    }

    // Validate Birthdate
    if (empty($birthdate)) {
        $errors[] = 'Birthdate is required.';
    }

    // Handle profile picture upload validation
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB max size

        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Profile picture must be an image (JPG, PNG, GIF).';
        }

        // Validate file size
        if ($file['size'] > $max_file_size) {
            $errors[] = 'Profile picture must be less than 5MB.';
        }
    }

    // If there are validation errors, display them and stop further processing
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit; // Stop execution if there are errors
    }

    // Proceed with data processing if no errors
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Handle profile picture upload if valid
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $file_name = time() . '_' . $file['name']; // Generate unique file name
        $file_tmp = $file['tmp_name'];
        $file_dir = 'uploads/profile_pictures/';

        if (!file_exists($file_dir)) {
            mkdir($file_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $file_dir . $file_name)) {
            // Profile picture uploaded successfully
            $profile_picture = $file_name;
        } else {
            $profile_picture = null; // Set to null if upload fails
        }
    } else {
        $profile_picture = null; // No file uploaded
    }

    // Save user data into the database (example)
    $db = new mysqli($host, $username, '', $dbname);

    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    // Check for duplicate email
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $db->query($query);

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        exit; // Stop execution if email already exists
    }

    // Insert user data into the database
    $query = "INSERT INTO users (id_number, username, email, password, role, status, first_name, last_name, contact_number, birthdate, profile_picture) 
              VALUES ('$id_number', '$user_username', '$email', '$password_hashed', '$role', '$status', '$first_name', '$last_name', '$contact_number', '$birthdate', '$profile_picture')";

    if ($db->query($query)) {
        // Redirect to user management page on success
        header('Location: manage_users.php?message=User added successfully.');
        exit; // Ensure no further code is executed after the redirect
    } else {
        // Handle database error
        echo json_encode(['success' => false, 'message' => 'Error adding user to the database.']);
    }

    // Close DB connection
    $db->close();
}
?>
