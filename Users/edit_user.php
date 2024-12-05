<?php
session_start();
require 'includes/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit();
}

$user_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: manage_users.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count == 0) {
   
        $sql = "UPDATE users SET username = :username, role = :role";
        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
        }
        $sql .= " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        if ($password) {
            $stmt->bindParam(':password', $hashed_password);
        }
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        echo '<script>alert("User Account Edited Successfully"); window.location.href = "manage_users.php";</script>';
        exit();
    } else {
        $error = "Username already exists.";
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
                        <a href="generate_request.php">
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
                    <a href="generate_request.php">
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

 <div class="flex-1 flex flex-col items-center justify-center ml-64 pt-20">
    <div class="bg-white bg-opacity-70 rounded-lg p-8 shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-semibold mb-6 text-center">Edit User</h2>
        
        <!-- Error message -->
        <?php if (!empty($error)): ?>
            <p class="text-red-600 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form action="edit_user.php?id=<?php echo $user_id; ?>" method="POST" onsubmit="return confirmAddition();">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                       placeholder="Username" required class="w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"/>
            </div>

            <div class="mb-4 relative">
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" placeholder="Leave blank to keep current password" 
                       class="w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-gray-600 mt-2.5" onclick="togglePassword()">Show</span>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" required class="w-full px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Admin" <?php echo $user['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="Property Custodian" <?php echo $user['role'] == 'Property Custodian' ? 'selected' : ''; ?>>Property Custodian</option>
                    <option value="Inspector" <?php echo $user['role'] == 'Inspector' ? 'selected' : ''; ?>>Inspector</option>
                    <option value="Faculty" <?php echo $user['role'] == 'Faculty' ? 'selected' : ''; ?>>Faculty</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">Edit User</button>
            <a href="manage_users.php" class="block text-center mt-4 text-blue-500 hover:text-blue-700">Go Back</a>
        </form>
    </div>
</div>

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
</body>
</html>

