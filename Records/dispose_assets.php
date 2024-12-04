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

// Handle the restore action
if (isset($_GET['action']) && $_GET['action'] === 'restore' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("UPDATE assets SET disposed = 0 WHERE id = ?");
        $stmt->execute([$id]);

        echo '<script>alert("Asset restored successfully."); window.location.href = "dispose_assets.php";</script>';
    } catch (PDOException $e) {
        echo '<script>alert("Error performing action: ' . $e->getMessage() . '"); window.location.href = "dispose_assets.php";</script>';
    }

    exit();
}

$sql = "SELECT 
            assets.asset_id AS id,  
            assets.name AS asset_name,  
            categories.name AS categories, 
            rooms.name AS room_name, 
            users.username AS users_username, 
            assets.asset_count, 
            assets.qrcode, 
            assets.comments, 
            assets.disposal_date, 
            assets.is_disposed, 
            assets.model, 
            assets.specs, 
            assets.status, 
            assets.last_inspected, 
            assets.last_updated_by, 
            assets.updated_at
        FROM assets
        LEFT JOIN categories ON assets.category_id = categories.category_id
        LEFT JOIN rooms ON assets.room_id = rooms.room_id
        LEFT JOIN users ON assets.person_in_charge_id = users.user_id
        WHERE assets.is_disposed = 'Requested';";


$stmt = $conn->prepare($sql);
$stmt->execute();
$disposed_assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disposed Assets</title>

    <script src="https://cdn.tailwindcss.com"></script>
 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</head>
<style>
          @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

* {
    font-family: 'Poppins', sans-serif;
}
</style>


<script>
    function confirmAction(message) {
        return confirm(message);
    }

    $(document).ready(function() {
        $('#disposedAssetsTable').DataTable({
            "pageLength": 10, // Set the number of records to display per page
            "order": [[ 8, "desc" ]] // Order by disposal date by default
        });
    });
</script>
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
                    <a href="../dashboard.php">
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
                        <a href="../Reports/reports.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Person-In-Charge</button>
                        </a>
                        <a href="../Reports/asset_durability.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Asset Durability</button>
                        </a>
                    </div>

                    <!-- User Management for Admin Only -->
                    <?php if ($role == 'Admin'): ?>
                        <a href="../Users/manage_users.php">
                            <button class="nav-icon fas fa-id-card text-white text-sm"> User Management</button>
                        </a>
                    <?php endif; ?>

                    <!-- Log Out -->
                    <a href="../logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>

                <?php elseif ($role == 'Faculty'): ?>
                    <!-- Faculty Specific Options -->
                    <a href="../Reports/reports.php">
                        <button class="nav-icon fas fa-folder text-white text-sm"> Reports</button>
                    </a>
                    <a href="../Reports/procurement.php">
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
      

    
        <div class="flex-1 flex flex-col items-start justify-start ml-64">
  

        <div class="w-full max-w-7xl rounded-xl mx-auto p-6 mt-3 bg-white">
    
        <h2 class="text-2xl font-bold">Disposed Assets</h2>
  
    <div class="overflow-x-auto">
        <table id="disposedAssetsTable" class="w-full bg-white border border-gray-200">
            <thead>
                <tr>
           
                    <th class="text-left text-sm p-4 border-b">Brand</th>
                    <th class="text-left text-sm p-4 border-b">Model</th>
                    <th class="text-left text-sm p-4 border-b">Room Type</th>
                    <th class="text-left text-sm p-4 border-b">Room</th>
                    <th class="text-left text-sm p-4 border-b">Person In Charge</th>
                    <th class="text-left text-sm p-4 border-b">Remarks</th>
                    <th class="text-left text-sm p-4 border-b">QR Code</th>
                    <th class="text-left text-sm p-4 border-b">Disposal Date</th>
                    <th class="text-left text-sm p-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disposed_assets as $asset): ?>
                    <tr>
                       
                        <td class="p-4 text-sm border-b"><?php echo htmlspecialchars($asset['asset']); ?></td>
                        <td class="p-4 text-sm border-b"><?php echo htmlspecialchars($asset['model']); ?></td>

                        <td class="p-4 text-sm border-b"><?php echo htmlspecialchars($asset['room_type']); ?></td>
                        <td class="p-4 text-sm border-b"><?php echo htmlspecialchars($asset['room']); ?></td>
                        <td class="p-4 text-sm border-b"><?php echo htmlspecialchars($asset['person_in_charge']); ?></td>
                        <td class="p-4 text-sm border-b"><?php echo htmlspecialchars($asset['comments']); ?></td>
                        <td class="p-4 text-sm border-b">
                            <?php if ($asset['qrcode']): ?>
                                <img src="<?php echo htmlspecialchars($asset['qrcode']); ?>" alt="QR Code" class="qrCodeImage w-12 h-12">
                            <?php else: ?>
                                <span>No QR Code</span>
                            <?php endif; ?>
                        </td>
                        <div id="qrCodeModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-black bg-opacity-50 absolute inset-0"></div>
    <div class="bg-white p-4 rounded-lg shadow-lg z-10 relative">
        <span id="closeModal" class="absolute top-2 right-2 text-gray-600 cursor-pointer">&times;</span>
        <img src="<?php echo htmlspecialchars($asset['qrcode']); ?>" alt="QR Code Large" class="h-64 w-64">
    </div>
</div>

<script>
$(document).ready(function() {

    $('.qrCodeImage').on('click', function() {
        $('#qrCodeModal').removeClass('hidden');
    });

    $('#closeModal').on('click', function() {
        $('#qrCodeModal').addClass('hidden');
    });

    $('#qrCodeModal').on('click', function(event) {
        if (event.target.id === 'qrCodeModal') {
            $('#qrCodeModal').addClass('hidden');
        }
    });
});

</script>      
                        <td class="p-4 border-b"><?php echo htmlspecialchars($asset['disposal_date']); ?></td>
                        <td class="p-4 border-b ">
                            <div class="flex space-x-2">
                            <a href="dispose_assets.php?action=restore&id=<?php echo $asset['asset_record_id']; ?>" class="text-blue-600 hover:text-blue-800 mt-3.5" onclick="return confirmAction('Are you sure you want to restore this record?');">Restore</a>
                            <form action="generate disposed.php" method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars( $asset['asset_record_id'] );?>">
                                <button type="submit" class="text-white bg-blue-500 hover:bg-blue-700 px-2 py-1 rounded">Generate PDF</button>
                            </form>
                                                 </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


</div>
      
    </div>


    <div class="absolute bottom-0 left-0 right-0 flex justify-center space-x-8 p-4">
        <a href="#" class="text-sm font-bold text-white">HELP</a>
    </div>
</body>
</html>