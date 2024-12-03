<?php
include 'includes/db.php'; // Include your database connection settings

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $fname = $_POST['first_name'];
    $sname = $_POST['last_name'];
    $contact_num = $_POST['contact_number'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $id_num = $_POST['id_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    

    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {

        $conn->beginTransaction();


        $sql = "INSERT INTO users (username, password, role, created_at, status) 
                VALUES (:username, :password, :role, NOW(), 'Active')";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $email);
        $stmt->bindParam(':password', $hashed_password);
        $role = 'Faculty'; 
        $stmt->bindParam(':role', $role);
        $stmt->execute();

        $user_id = $conn->lastInsertId();

  
        $sql = "INSERT INTO users(first_name, last_name, contact_number, email, birthdate, id_number, user_id) 
                VALUES (:fname, :sname, :contact_num, :email, :birthdate, :id_num, :user_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':first_name', $fname);
        $stmt->bindParam(':last_name', $sname);
        $stmt->bindParam(':contact_number', $contact_num);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':id_number', $id_num);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

    
        $conn->commit();

        header("Location: register.php?success=User registered Successfully!");
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @layer utilities {
            .bg-split {
                background: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 0%), url('images/Background.png');
background-size: cover;
background-position: center;

            }
        }
    </style>
</head>
<body class="h-screen flex items-center justify-end bg-split">
<div class="absolute flex left-10 top-5 text-white items-center">
    <div class="flex items-center">
        <img src="images/DAZSMALOGO.png" alt="Logo" class="mb-4 w-36 h-36 mr-4">
        <div class="flex-col">
            <h1 class="text-5xl font-bold">DAZSMA-SAMS</h1>
            <p class="text-xl">School Assets Management System</p>
        </div>
    </div>
</div>
            <div class="flex justify-center items-center h-screen mr-20">
                    <div class="bg-white bg-opacity-30 rounded-3xl p-8 shadow-lg w-full max-w-lg">
                    <?php if (isset($_GET['error'])) { ?>
    <div id="alert-2" class="error flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
  <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
  </svg>
  <span class="sr-only">Info</span>
  <div class="ms-3 text-sm font-medium">
  <?php echo $_GET['error']; ?>
  </div>
  <button id="closeErrorBtn" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-2" aria-label="Close">
    <span class="sr-only">Close</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
    </svg>
  </button>
</div>      
<script>
   
    const closeErrorBtn = document.getElementById('closeErrorBtn');
    const errorContainer = document.querySelector('.error');

    closeErrorBtn.addEventListener('click', () => {
        errorContainer.style.display = 'none'; 
       
        const url = new URL(window.location.href);
        url.searchParams.delete('error');
        history.replaceState({}, document.title, url);
    });

    setTimeout(() => {
        errorContainer.style.display = 'none';

        const url = new URL(window.location.href);
        url.searchParams.delete('error');
        history.replaceState({}, document.title, url);
    }, 7000); 
</script>
     	           <?php } ?>
                  <?php if (isset($_GET['success'])) { ?>
                    <div id="alert-1" class="success flex items-center p-4 mb-4 text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
  <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
  </svg>
  <span class="sr-only">Info</span>
  <div class="ms-3 text-sm font-medium">
  <?php echo $_GET['success']; ?>
  </div>
    <button id="closeSuccessBtn" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-1" aria-label="Close">
      <span class="sr-only">Close</span>
      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
      </svg>
  </button>
</div>   
<script>
    const closesuccessBtn = document.getElementById('closeSuccessBtn');
    const successContainer = document.querySelector('.success');

    closesuccessBtn.addEventListener('click', () => {
        successContainer.style.display = 'none';
        
        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        history.replaceState({}, document.title, url);
    });


    setTimeout(() => {
        successContainer.style.display = 'none';

        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        history.replaceState({}, document.title, url);
    }, 7000); 
</script>
     	           <?php } ?>
                 <form method="POST" action="register.php">

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-white">First Name</label>
                        <input type="text" name="fname" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                    <div>
                        <label class="block text-white">Surname</label>
                        <input type="text" name="sname" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-white">Contact Number</label>
                        <input type="text" name="contact_num" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                    <div>
                        <label class="block text-white">Email</label>
                        <input type="email" name="email" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-white">Birth Date</label>
                        <input type="date" name="birthdate" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                    <div>
                        <label class="block text-white">ID Number</label>
                        <input type="text" name="id_num" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-white">Set Password</label>
                        <input type="password" name="password" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                    <div>
                        <label class="block text-white">Confirm Password</label>
                        <input type="password" name="confirm_password" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80" required>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-2xl w-1/2">
                        SIGN UP
                    </button>
                </div>

                <div class="text-center text-white text-sm">
                    <p>Already have an account? <a href="login.php" class="underline">Log in here.</a></p>
                    <p class="mt-2 text-xs">If an error occurs while filling out the form, a message will show up here.</p>
                </div>
            </form>

        </div>
    </div>
</body>
</html>