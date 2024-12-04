<?php
session_start();
require '../includes/db.php';
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

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

// Fetch options for filters
function fetchOptions($table, $column)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$categories = fetchOptions('categories', 'name');
$sub_categories = fetchOptions('sub_categories', 'name');
$room_types = fetchOptions('room_types', 'name');
$rooms = fetchOptions('rooms', 'name');
$persons_in_charge = fetchOptions('persons_in_charge', 'name');

// Apply filters
$where = [];
$params = [];

if (!empty($_GET['category'])) {
    $where[] = 'assets.category_id = :category';
    $params[':category'] = $_GET['category'];
}

if (!empty($_GET['sub_category'])) {
    $where[] = 'assets.sub_category_id = :sub_category';
    $params[':sub_category'] = $_GET['sub_category'];
}

if (!empty($_GET['room_type'])) {
    $where[] = 'room_types.id = :room_type';
    $params[':room_type'] = $_GET['room_type'];
}

if (!empty($_GET['room'])) {
    $where[] = 'rooms.id = :room';
    $params[':room'] = $_GET['room'];
}

if (!empty($_GET['person_in_charge'])) {
    $where[] = 'persons_in_charge.id = :person_in_charge';
    $params[':person_in_charge'] = $_GET['person_in_charge'];
}

// Fetch assets
$sql = "SELECT  
            assets.asset_id AS user_id,  
            assets.name AS asset_name,  
            categories.name AS category, 
            rooms.name AS room_name, 
            users.username AS user_name, 
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


if (!empty($where)) {
    $sql .= " AND " . implode(' AND ', $where);
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle asset actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    try {
        if ($action === 'delete' && ($role === 'Admin' || $role === 'Property Custodian')) {
            // Fetch QR code filename before deleting the record
            $stmt = $conn->prepare("SELECT qrcode FROM asset_records WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record && file_exists($record['qrcode'])) {
                unlink($record['qrcode']); // Delete QR code file from the server
            }

            // Delete the record from the database
            $stmt = $conn->prepare("DELETE FROM asset_records WHERE id = ?");
            $stmt->execute([$id]);
            echo '<script>alert("Record Deleted Successfully"); window.location.href = "view_assets.php";</script>';
        } elseif ($action === 'dispose') {
            $stmt = $conn->prepare("UPDATE asset_records SET disposed = 'Requested', disposal_date = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            echo '<script>alert("Asset marked as disposed successfully."); window.location.href = "view_assets.php";</script>'; // Redirect to dispose_assets.php
        } elseif ($action === 'approve' && $role === 'Approver') {
            // Your logic for approving a request
            $stmt = $conn->prepare("UPDATE procurement_requests SET status = 'Approved' WHERE id = ?");
            $stmt->execute([$id]);
            echo '<script>alert("Request approved successfully."); window.location.href = "view_requests.php";</script>';
        } elseif ($action === 'deny' && $role === 'Approver') {
            // Your logic for denying a request
            $stmt = $conn->prepare("UPDATE procurement_requests SET status = 'Denied' WHERE id = ?");
            $stmt->execute([$id]);
            echo '<script>alert("Request denied successfully."); window.location.href = "view_requests.php";</script>';
        }
    } catch (PDOException $e) {
        echo '<script>alert("Error performing action: ' . $e->getMessage() . '"); window.location.href = "view_assets.php";</script>';
    }

    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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
    </script>
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
                        <a href="reports.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Person-In-Charge</button>
                        </a>
                        <a href="asset_durability.php">
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
      

    
        <div class="flex-1 flex flex-col items-center ml-64">
  
<div class="assets-table mt-8 p-4 rounded-lg bg-white">
<div class="filter-container p-6 bg-card rounded-lg ">
    <h2 class="text-xl font-semibold mb-4">View Assets</h2>
    <form action="view_assets.php" method="GET" class="space-y-4">
        <div class="flex flex-row">
        <div class="mr-4">
            <label for="category" class="block text-sm font-medium text-gray-700">Category:</label>
            <select id="category" name="category" class="block text-sm  w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div  class="mr-4">
            <label for="sub_category" class="block text-sm font-medium text-gray-700">Sub-Category:</label>
            <select id="sub_category" name="sub_category" class="block w-full text-sm mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Sub-Category</option>
                <?php foreach ($sub_categories as $sub_cat): ?>
                    <option value="<?php echo htmlspecialchars($sub_cat['id']); ?>" <?php echo (isset($_GET['sub_category']) && $_GET['sub_category'] == $sub_cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sub_cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div  class="mr-4">
            <label for="room_type" class="block text-sm font-medium text-gray-700">Room Type:</label>
            <select id="room_type" name="room_type" class="block w-full text-sm  mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Room Type</option>
                <?php foreach ($room_types as $room_type): ?>
                    <option value="<?php echo htmlspecialchars($room_type['id']); ?>" <?php echo (isset($_GET['room_type']) && $_GET['room_type'] == $room_type['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($room_type['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div  class="mr-4">
            <label for="room" class="block text-sm font-medium text-gray-700">Room:</label>
            <select id="room" name="room" class="block w-full mt-1 text-sm  border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Room</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?php echo htmlspecialchars($room['id']); ?>" <?php echo (isset($_GET['room']) && $_GET['room'] == $room['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($room['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mr-4">
            <label for="person_in_charge" class="block text-sm font-medium text-gray-700">Person In Charge:</label>
            <select id="person_in_charge" name="person_in_charge" class="block text-sm  w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Person In Charge</option>
                <?php foreach ($persons_in_charge as $person): ?>
                    <option value="<?php echo htmlspecialchars($person['id']); ?>" <?php echo (isset($_GET['person_in_charge']) && $_GET['person_in_charge'] == $person['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($person['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
       
        <div class="flex">
            <button type="submit"class="bg-blue-600 hover:bg-blue-700 w-full  text-white font-bold py-2 ">Filter</button>

        </div>
        </div>
    </form>
</div>
    <?php if (isset($_SESSION['success_message'])): ?>
        <p class="success-message bg-green-100 text-green-800 p-4 rounded-md mb-4"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <p class="error-message bg-red-100 text-red-800 p-4 rounded-md mb-4"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 text-sm  py-2 text-left text-sm font-semibold text-gray-700">Category</th>
                <th class="px-4 text-sm py-2 text-left text-sm font-semibold text-gray-700">Sub-Category</th>
                <th class="px-4 text-sm  py-2 text-left text-sm font-semibold text-gray-700">Item</th>
                <th class="px-4 text-sm  py-2 text-left text-sm font-semibold text-gray-700">Room Type</th>
                <th class="px-4 text-sm  py-2 text-left text-sm font-semibold text-gray-700">Room</th>
                <th class="px-4 text-sm  py-2 text-left text-sm font-semibold text-gray-700">Person In Charge</th>
                <th class="px-4 text-sm  py-2 text-left text-sm font-semibold text-gray-700">QR Code</th>
                <th class="px-4 text-sm  py-2 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($assets as $asset): ?>
                <tr>
                    <td class="px-4 text-sm  py-2"><?php echo htmlspecialchars($asset['category']); ?></td>
                    <td class="px-4 text-sm  py-2"><?php echo htmlspecialchars($asset['sub_category']); ?></td>
                    <td class="px-4 text-sm  py-2"><?php echo htmlspecialchars($asset['asset']); ?></td>
                    <td class="px-4 text-sm  py-2"><?php echo htmlspecialchars($asset['room_type']); ?></td>
                    <td class="px-4 text-sm  py-2"><?php echo htmlspecialchars($asset['room']); ?></td>
                    <td class="px-4 text-sm  py-2"><?php echo htmlspecialchars($asset['person_in_charge']); ?></td>

<td class="px-4 text-sm py-2">
    <img src="<?php echo htmlspecialchars($asset['qrcode']); ?>" alt="QR Code" class="h-12 w-12 cursor-pointer qrCodeImage" >
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

</script>                    <td class="px-4 py-2">
    <div class="flex space-x-2">
        <?php if ($role == 'Admin' || $role == 'Property Custodian'): ?>
            <a href="inspect_asset.php?id=<?php echo $asset['asset_record_id']; ?>" class="bg-blue-500 text-white px-2 py-1 rounded-md shadow-md hover:bg-blue-600" title="Inspect this asset">
                <i class="fas fa-search"></i> 
            </a>
         
            <a href="transfer_asset.php?id=<?php echo $asset['asset_record_id']; ?>" class="bg-yellow-500 text-white px-2 py-1 rounded-md shadow-md hover:bg-yellow-600" title="Transfer this asset">
                <i class="fas fa-exchange-alt"></i> 
            </a>
            <a href="view_assets.php?action=dispose&id=<?php echo $asset['asset_record_id']; ?>" class="bg-red-500 text-white px-2 py-1 rounded-md shadow-md hover:bg-red-600" title="Dispose this asset" onclick="return confirmAction('Are you sure you want to mark this asset as disposed?');">
                <i class="fas fa-trash-alt"></i> 
            </a>
            <a href="view_assets.php?action=delete&id=<?php echo $asset['asset_record_id']; ?>" class="bg-red-600 text-white px-2 py-1 rounded-md shadow-md hover:bg-red-700" title="Delete this asset" onclick="return confirmAction('Are you sure you want to delete this asset? This action cannot be undone.');">
                <i class="fas fa-times"></i> 
            </a>
        <?php elseif ($role == 'Inspector'): ?>
            <a href="inspect_asset.php?id=<?php echo $asset['asset_record_id']; ?>" class="bg-blue-500 text-white px-2 py-1 rounded-md shadow-md hover:bg-blue-600" title="Inspect this asset">
                <i class="fas fa-search"></i>
            </a>
        <?php endif; ?>
        <?php if ($role == 'Approver'): ?>
            <a href="view_assets.php?action=approve&id=<?php echo $asset['asset_record_id']; ?>" class="bg-green-500 text-white px-2 py-1 rounded-md shadow-md hover:bg-green-600" onclick="return confirmAction('Are you sure you want to approve this request?');">
                <i class="fas fa-check"></i>
            </a>
            <a href="view_assets.php?action=deny&id=<?php echo $asset['asset_record_id']; ?>" class="bg-red-500 text-white px-2 py-1 rounded-md shadow-md hover:bg-red-600" onclick="return confirmAction('Are you sure you want to deny this request?');">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </div>
</td>


                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


      
    </div>


    
</body>
<script>
$(document).ready(function() {
    $('.min-w-full').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "pageLength": 10
    });
});
</script>

</html>
