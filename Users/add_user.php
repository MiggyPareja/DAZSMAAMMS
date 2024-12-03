<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'], $_POST['role'])) {
    $fname = $_POST['fname'];
    $sname = $_POST['sname'];
    $contact_num = $_POST['contact_num'];
    $email = $_POST['email'];
    $roles = $_POST['role'];
    $birthdate = $_POST['birthdate'];
    $id_num = $_POST['id_num'];
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
        $stmt->bindParam(':role', $roles);

        $stmt->execute();

        $user_id = $conn->lastInsertId();

  
        $sql = "INSERT INTO user_information (fname, sname, contact_num, email, birthdate, id_num, id) 
                VALUES (:fname, :sname, :contact_num, :email, :birthdate, :id_num, :user_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fname', $fname);
        $stmt->bindParam(':sname', $sname);
        $stmt->bindParam(':contact_num', $contact_num);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':id_num', $id_num);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

    
        $conn->commit();

        echo '<script>alert("User Added Successfully"); window.location.href = "manage_users.php";</script>';

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
    <title>DAZSMA Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
          @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

* {
    font-family: 'Poppins', sans-serif;
}
</style>
</head>

<body class="bg-cover bg-center h-screen"
    style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('images/Background.png');">
        <a>
        <div class="bg-blue-900 w-64 flex flex-col p-4 fixed h-full space-y-4 z-20">
              <div class="image">
                  <img src="images/SYSTEM LOGO 2.png" alt="User Image" class="text-white text-left">
              </div>
        </a>



        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
              <div class="image">
                  <img src="images/avatar.png" class="rounded-full w-12 h-12" alt="User Image">
              </div>
              <div class="info">
                  <a href="#" class="d-block text-white"><?php echo $username; ?></a>
              </div>
          </div>
            <nav class="flex flex-col space-y-4">
                <?php if ($role == 'Admin' || $role == 'Property Custodian' || $role == 'Inspector'): ?>
                    <!-- Dashboard -->
                    <a href="dashboard.php">
                        <button class="nav-icon fas fa-tachometer-alt text-white text-sm"> Dashboard</button>             
                    </a>

                    <!-- Collapsible Records Section -->
                    <a>
                    <button id="recordsBtn" class="nav-icon fas fa-folder text-white text-sm"> Records
    <span id="arrow" class="transform transition-transform">&#9660;</span>
</button></a>
                    <div id="recordsMenu" class="hidden flex flex-col p-2 space-y-3">
                        <a href="view_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Assets</button>
                        </a>
                        <?php if ($role == 'Admin' || $role == 'Property Custodian'): ?>
                        <a href="dispose_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Disposed Assets</button>
                        </a>
                        <a href="add_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Deploy Assets</button>
                        </a>
                        <?php endif; ?>
                        <a href="view_request.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Requests</button>
                        </a>
                        <a href="procurement.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Generate Request</button>
                        </a>
                    </div>
                    
                    <!-- Reports -->
                    <a>
                    <button id="reportsBtn" class="nav-icon fas fa-chart-bar text-white text-sm"> Reports
    <span id="arrow" class="transform transition-transform">&#9660;</span>
</button></a>
                    <div id="reportsMenu" class="hidden flex flex-col p-2 space-y-3">
                        <a href="reports.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Person-In-Charge</button>
                        </a>
                        <a href="asset_durability.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Asset Durability</button>
                        </a>
                    </div>

                    <!-- User Management for Admin Only -->
                    <?php if ($role == 'Admin'): ?>
                        <a href="manage_users.php">
                            <button class="nav-icon fas fa-id-card text-white text-sm"> User Management</button>
                        </a>
                    <?php endif; ?>

                    <!-- Log Out -->
                    <a href="logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>

                <?php elseif ($role == 'Faculty'): ?>
                    <!-- Faculty Specific Options -->
                    <a href="reports.php">
                        <button class="nav-icon fas fa-folder text-white text-sm"> Reports</button>
                    </a>
                    <a href="procurement.php">
                        <button class="far fa-circle nav-icon text-white text-sm"> Generate Request</button>
                    </a>
                    <a href="logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>
                <?php endif; ?>
            </nav>
        </div>

      
<script>
        document.getElementById('recordsBtn').addEventListener('click', function () {
            const recordsMenu = document.getElementById('recordsMenu');
            const arrow = document.getElementById('arrow');
            recordsMenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });

        document.getElementById('reportsBtn').addEventListener('click', function () {
            const reportsMenu = document.getElementById('reportsMenu');
            const arrow = document.getElementById('arrow');
            reportsMenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });
</script>
      

    
        <div class="flex-1 flex flex-col items-center justify-center ml-64 top-0">
  
        <div class="bg-white bg-opacity-30 rounded-3xl p-8 shadow-lg w-full max-w-lg">
        <?php if (!empty($error))
            echo '<p class="error">' . $error . '</p>'; ?>
            <form  action="add_user.php" method="POST" onsubmit="return confirmAddition();">
            <h1 class="text-2xl text-white font-bold text-center">Add User</h1>
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

                <div class="mb-4">
                    <label class="block text-white">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-white">Contact Number</label>
                        <input type="text"  name="contact_num" class="w-full p-2 border rounded-2xl bg-white bg-opacity-80">
                    </div>
                    <div>
                        <label class="block text-white">Role</label>
                        <select name="role" required class="w-full p-2 border rounded-2xl bg-white bg-opacity-80">
                        <option disabled selected>Select Role</option>
                        <option>Admin</option>
                        <option >Property Custodian</option>
                        <option >Inspector</option>
                        <option >Faculty</option>
                      

                        </select>
                   
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

                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-2xl w-1/2">
                        SIGN UP
                    </button>
                    <a href="manage_users.php" class="block text-center mt-4 text-white">Go Back</a>
                </div>

            
                
            </form>
        </div>
        </div>
        <div class="w-46 flex flex-col p-4 space-y-4 z-20">
           
            <img src="images/DAZSMALOGO.png" alt="School Logo" class="w-48 h-48">

        </div>

      
    </div>


    <div class="absolute bottom-0 left-0 right-0 flex justify-center space-x-8 p-4">
        <a href="#" class="text-sm font-bold text-white">HELP</a>
    </div>
</body>

</html>
<script>
        function confirmAddition() {
            return confirm("Are you sure you want to add this user?");
        }

        function togglePassword() {
            var passwordField = document.getElementById('password');
            var togglePasswordText = document.querySelector('.toggle-password');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePasswordText.textContent = 'Hide';
            } else {
                passwordField.type = 'password';
                togglePasswordText.textContent = 'Show';
            }
        }
    </script>