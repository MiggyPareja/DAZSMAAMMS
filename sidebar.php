<?php
session_start();
require __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch username from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$lastname = $user ? htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') : 'Unknown User';
$firstname = $user ? htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') : 'Unknown User';
$idNumber = $user ? htmlspecialchars($user['id_number'], ENT_QUOTES, 'UTF-8') : 'Unknown User';
$profile_picture = $user && !empty($user['profile_picture']) ? '../Users/uploads/profile_pictures/' . htmlspecialchars($user['profile_picture'], ENT_QUOTES, 'UTF-8') : '../images/avatar.png';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <link rel="icon" href="images\DAZSMALOGO.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-cover bg-center h-screen" style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('../images/Background.png');">

    <div class="bg-blue-900 w-64 flex flex-col p-4 fixed h-full space-y-4 z-20">
    <div class="image">
        <img src="../images/SYSTEM LOGO 2.png" alt="System Logo" class="text-white text-left">
    </div>

    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="<?php echo $profile_picture; ?>" class="w-36 h-24 rounded-full" alt="User Image">
        </div>
        <div class="info">
            <a href="#" class="d-block text-white text-lg font-semibold">
                <?php echo $lastname . ', ' . $firstname; ?>
            </a>
        </div>
    </div>

    <nav class="flex flex-col space-y-4">
        <?php if ($role == 'Admin'): ?>
            <!-- Dashboard -->
            <a href="../dashboard.php" class="flex items-center space-x-2 text-white hover:text-gray-300">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>

            <!-- Collapsible Assets Section -->
            <a class="flex items-center space-x-2 text-white hover:text-gray-300 cursor-pointer" id="recordsBtn">
                <i class="fas fa-folder"></i>
                <span>Assets</span>
                <span id="recordsArrow" class="ml-auto transform transition-transform">&#9660;</span>
            </a>
            <div id="recordsMenu" class="hidden flex flex-col p-2 space-y-3 pl-6">
                <a href="../Records/generate_request.php" class="text-white text-sm hover:text-gray-300">
                    <i class="far fa-circle"></i> Generate Request
                </a>
                <a href="../Records/view_request.php" class="text-white text-sm hover:text-gray-300">
                    <i class="far fa-circle"></i> View Requests
                </a>
                <a href="../Records/add_assets.php" class="text-white text-sm hover:text-gray-300">
                    <i class="far fa-circle"></i> Inventory
                </a>
                <a href="../Records/deployed_assets.php" class="text-white text-sm hover:text-gray-300">
                    <i class="far fa-circle"></i> Deployed Assets
                </a>
                <a href="../Records/disposedAssets.php" class="text-white text-sm hover:text-gray-300">
                    <i class="far fa-circle"></i> Disposed Assets
                </a>
            </div>

            <!-- Collapsible Logs Section -->
            <a class="flex items-center space-x-2 text-white hover:text-gray-300 cursor-pointer" id="logsBtn">
                <i class="fas fa-folder"></i>
                <span>Logs</span>
                <span id="logsArrow" class="ml-auto transform transition-transform">&#9660;</span>
            </a>
            <div id="logsMenu" class="hidden flex flex-col p-2 space-y-3 pl-6">
                <a href="../Records/activity_logs.php" class="text-white text-sm hover:text-gray-300">
                    <i class="far fa-circle"></i> Activity Logs
                </a>
                <a href="../Records/transfer_logs.php" class="text-white text-sm hover:text-gray-300">
                    <i class="far fa-circle"></i> Transfer Logs
                </a>
            </div>

            <!-- User Management -->
            <a href="../Users/manage_users.php" class="flex items-center space-x-2 text-white hover:text-gray-300">
                <i class="fas fa-id-card"></i>
                <span>User Management</span>
            </a>

            <!-- Log Out -->
            <a href="../logout.php" onclick="confirmLogout(event)" class="flex items-center space-x-2 text-white hover:text-gray-300">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log Out</span>
            </a>
        <?php elseif ($role == 'Faculty'): ?>
            <!-- Faculty Section -->
            <a href="../Records/generate_request.php" class="flex items-center space-x-2 text-white hover:text-gray-300">
                <i class="far fa-circle"></i>
                <span>Generate Request</span>
            </a>
            <!-- Log Out -->
            <a href="../logout.php" onclick="confirmLogout(event)" class="flex items-center space-x-2 text-white hover:text-gray-300">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log Out</span>
            </a>
        <?php elseif ($role == 'Inspector'): ?>
            <!-- Inspector Section -->
            <a href="../Records/deployed_assets.php" class="flex items-center space-x-2 text-white hover:text-gray-300">
                <i class="far fa-circle"></i>
                <span>Deployed Assets</span>
            </a>
            <!-- Log Out -->
            <a href="../logout.php" onclick="confirmLogout(event)" class="flex items-center space-x-2 text-white hover:text-gray-300">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log Out</span>
            </a>
        <?php endif; ?>
    </nav>
</div>



    <script>
        // Toggle functionality for Assets dropdown
        document.getElementById('recordsBtn').addEventListener('click', function () {
            const recordsMenu = document.getElementById('recordsMenu');
            const recordsArrow = document.getElementById('recordsArrow');
            recordsMenu.classList.toggle('hidden');
            recordsArrow.classList.toggle('rotate-180');
        });

        // Toggle functionality for Logs dropdown
        document.getElementById('logsBtn').addEventListener('click', function () {
            const logsMenu = document.getElementById('logsMenu');
            const logsArrow = document.getElementById('logsArrow');
            logsMenu.classList.toggle('hidden');
            logsArrow.classList.toggle('rotate-180');
        });

        // Confirmation for log out
        function confirmLogout(event) {
            if (!confirm("Are you sure you want to log out?")) {
                event.preventDefault();
            }
        }
    </script>

</body>
</html>
