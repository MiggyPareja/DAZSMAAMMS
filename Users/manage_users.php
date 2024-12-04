<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';

// Fetch user records from the database
$stmt = $conn->prepare("SELECT 
    users.user_id, 
    users.username, 
    users.role, 
    users.first_name,
    users.Last_name
FROM 
    users

WHERE 
    users.status = 'Active';
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("UPDATE users SET status='Deactivate' WHERE id = :id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->execute();
    echo '<script>alert("User Deactiviated Successfully"); window.location.href = "manage_users.php";</script>';
    exit();
}
?>


<script>
    function confirmAction(message) {
        return confirm(message);
    }
</script>

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
    
        <div class="flex-1 flex flex-col items-center justify-center top-0 ml-64">
  
        <div class="bg-white rounded-xl p-8 shadow-lg  w-full max-w-4xl">
        <?php if (!empty($error))
            echo '<p class="error">' . $error . '</p>'; ?>
               <div class="p-6 bg-white shadow-mdrounded-lg">
    <h2 class="text-2xl font-bold mb-4">Manage Users</h2>
    <div class="flex justify-between mb-4">
        <a href="add_user.php" class="bg-blue-500 text-white py-2 px-4 rounded-full hover:bg-blue-600 transition">Add User</a>
    </div>
    <table class="min-w-full bg-white border rounded-lg">
        <thead>
            <tr>
            <th class="py-2 px-4 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Fullname</th>

                <th class="py-2 px-4 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Email</th>
                <th class="py-2 px-4 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Role</th>
                <th class="py-2 px-4 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                <td class="py-2 px-4 border-b border-gray-300 text-sm text-gray-600"><?php echo htmlspecialchars($user['fname']).' '. htmlspecialchars($user['sname']); ?></td>

                    <td class="py-2 px-4 border-b border-gray-300 text-sm text-gray-600"><?php echo htmlspecialchars($user['username']); ?></td>
                    <td class="py-2 px-4 border-b border-gray-300 text-sm text-gray-600"><?php echo htmlspecialchars($user['role']); ?></td>
                    <td class="py-2 px-4 border-b border-gray-300 text-sm">
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700 transition">Edit</a>
                        <a href="manage_users.php?delete_id=<?php echo $user['id']; ?>" class="text-red-500 hover:text-red-700 transition ml-4" onclick="return confirmAction('Are you sure you want to Deactivate this Account?');">Deactivate</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
        </div>
        </div>
     
      
    </div>


    <div class="absolute bottom-0 left-0 right-0 flex justify-center space-x-8 p-4">
        <a href="#" class="text-sm font-bold text-white">HELP</a>
    </div>
</body>

</html>
