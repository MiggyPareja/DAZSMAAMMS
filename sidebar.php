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
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$lastname = $user ? htmlspecialchars($user['last_name']) : 'Unknown User';
$firstname = $user ? htmlspecialchars($user['first_name']) : 'Unknown User';
?>

<div class="bg-blue-900 w-64 flex flex-col p-4 fixed h-full space-y-4 z-20">
    <div class="image">
        <img src="..\images\SYSTEM LOGO 2.png" alt="System Logo" class="text-white text-left">
    </div>

    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="../images/avatar.png" class="rounded-full w-12 h-12" alt="User Image">
        </div>
        <div class="info">
            <a href="#" class="d-block text-white"><?php echo $lastname . ',' . $firstname; ?></a>
        </div>
    </div>

    <nav class="flex flex-col space-y-4">
        <?php if ($role == 'Admin' || $role == 'Property Custodian' || $role == 'Inspector'): ?>
            <!-- Dashboard -->
            <a href="../dashboard.php">
                <button class="nav-icon fas fa-tachometer-alt text-white text-sm"> Dashboard</button>             
            </a>

            <!-- Collapsible Records Section -->
            <a>
                <button id="recordsBtn" class="nav-icon fas fa-folder text-white text-sm"> Assets
                    <span id="arrow" class="transform transition-transform">&#9660;</span>
                </button>
            </a>
            <div id="recordsMenu" class="hidden flex flex-col p-2 space-y-3">
            <a href="../Records/procurement.php">
                    <button class="far fa-circle nav-icon text-white text-xs"> Generate Request</button>
            </a>
            <a href="../Records/view_request.php">
                    <button class="far fa-circle nav-icon text-white text-xs"> View Requests</button>
            </a>            
            <a href="../Records/add_assets.php">
                        <button class="far fa-circle nav-icon text-white text-xs"> Deploy Assets</button>
            </a>            
            <a href="../Records/view_assets.php">
                    <button class="far fa-circle nav-icon text-white text-xs"> View Assets</button>
            </a>
            <?php if ($role == 'Admin' || $role == 'Property Custodian'): ?>
                <a href="../Records/dispose_assets.php">
                <button class="far fa-circle nav-icon text-white text-xs"> View Disposed Assets</button>
                </a>

                <?php endif; ?>


            </div>
            
            <!-- Reports -->
            <a>
                <button id="reportsBtn" class="nav-icon fas fa-chart-bar text-white text-sm"> Reports
                    <span id="arrow" class="transform transition-transform">&#9660;</span>
                </button>
            </a>
            <div id="reportsMenu" class="hidden flex flex-col p-2 space-y-3">
                <a href="../Reports/reports.php">
                    <button class="far fa-circle nav-icon text-white text-xs"> Person-In-Charge</button>
                </a>
                <a href="../Reports/asset_durability.php">
                    <button class="far fa-circle nav-icon text-white text-xs"> Asset Durability</button>
                </a>
            </div>

            <!-- User Management for Admin Only -->
            <?php if ($role == 'Admin'): ?>
                <a href="../manage_users.php">
                    <button class="nav-icon fas fa-id-card text-white text-sm"> User Management</button>
                </a>
            <?php endif; ?>

            <!-- Log Out -->
            <a href="../logout.php" onclick="confirmLogout(event)">
                <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
            </a>

        <?php elseif ($role == 'Faculty'): ?>
            <!-- Faculty Specific Options -->
            <a href="../reports.php">
                <button class="nav-icon fas fa-folder text-white text-sm"> Reports</button>
            </a>
            <a href="../Records/procurement.php">
                <button class="far fa-circle nav-icon text-white text-sm"> Generate Request</button>
            </a>
            <a href="../logout.php" onclick="confirmLogout(event)">
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
