<?php
session_start();
require 'includes/db.php';
require 'includes/phpqrcode/qrlib.php';  // Include the QR code library

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>

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
      

    
        <div class="flex-1 flex flex-col items-center justify-center ml-64">
  
    <div class="max-w-2xl w-full bg-white shadow-lg rounded-lg p-6 mt-14">
        <div class="flex">
            <div class="flex-1">
            <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
                <h2 class="text-xl font-semibold mb-4">Inspection</h2>
                <form action="inspect_asset.php?id=<?php echo $asset_record_id; ?>" method="POST" onsubmit="return confirmAddition();">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Item:</label>
                    <input  type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"  id="asset_name" name="asset_name" value="<?php echo htmlspecialchars($asset['asset_name']); ?>" readonly>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Maintenance Type:</label>
                    <select  type="text" name="maintenance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
            <option disabled selected>Select maintenance type...</option>
            <option >Available</option>
            <option >Minor Repair</option>
            <option >Major Available</option>
            <option >Disposal</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Comment:</label>
                    <input id="comment" name="comment" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter Comment">
                </div>
                
                <div class="flex justify-start">
                <?php if (!empty($asset['qrcode'])): ?>
                <p><strong>QR Code:</strong></p>
                <img src="<?php echo htmlspecialchars($asset['qrcode']); ?>" alt="QR Code" class="h-24 w-24">
            <?php endif; ?>                </div>
            </div>
        <?php $query = $conn->prepare("
    SELECT c.comments, c.date, c.maintenance, u.username as inspector_name
    FROM comments c 
    JOIN users u ON c.userID = u.id
    
    WHERE c.id = ? ORDER BY c.date
");
$query->execute([$asset_record_id]);

$comments_data = $query->fetchAll(PDO::FETCH_ASSOC);


        ?>
<div class="ml-6 w-96 bg-blue-600 text-white p-4 rounded-lg">
    <h3 class="text-sm font-bold mb-4">Inspection History</h3>
    <?php foreach ($comments_data as $data): ?>
        <div class="bg-white text-black w-full py-2 px-4 mb-2 rounded-lg">
        <p class="mb-2">Maintenance type: <?php echo htmlspecialchars($data['maintenance']); ?></p>
            <p class="mb-2">Comment: <?php echo htmlspecialchars($data['comments']); ?></p>
            <p class="mb-2">Inspected by: <?php echo htmlspecialchars($data['inspector_name']); ?></p>
            <p class="text-xs text-right"><?php echo htmlspecialchars($data['date']); ?></p>
        </div>
    <?php endforeach; ?>
    <p class="text-xs mt-2 text-right">See more, refresh.</p>
</div>



        </div>
        <div class="flex justify-end mt-4">
            <button class="bg-blue-600 text-white py-2 px-4 rounded-lg mr-3 focus:outline-none focus:shadow-outline">Submit</button>
            <a href="view_assets.php" class="block text-center mt-2.5 text-black">Go Back</a>
        </div>
        </form>
    
</div>


</body>
</html>