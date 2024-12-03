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

// Fetch username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';
// Fetch the asset record details
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: view_assets.php');
    exit();
}

$asset_record_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT asset_records.id, 
           assets.name AS asset_name,
           categories.name AS category_name,
           sub_categories.name AS sub_category_name,
           room_types.name AS room_type_name,
           rooms.name AS room_name,
           persons_in_charge.name AS person_in_charge_name,
           asset_records.comments, 
           asset_records.last_inspected,
           asset_records.qrcode
    FROM asset_records
    JOIN assets ON asset_records.asset_id = assets.id
    JOIN categories ON assets.category_id = categories.id
    JOIN sub_categories ON assets.sub_category_id = sub_categories.id
    JOIN room_types ON asset_records.room_type_id = room_types.id
    JOIN rooms ON asset_records.room_id = rooms.id
    JOIN persons_in_charge ON asset_records.person_in_charge_id = persons_in_charge.id
    WHERE asset_records.id = ?
");
$stmt->execute([$asset_record_id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset) {
    header('Location: view_assets.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment']);
    $timestamp = date('Y-m-d H:i:s');
    $maintenance = trim($_POST['maintenance']);

    if (!empty($comment)) {
        try {
  
    $query = $conn->prepare("INSERT INTO `comments` (`id`, `comments`, `date`, maintenance, userID) 
    VALUES (?, ?, ?, ?, ?)");
    $query->execute([$asset_record_id, $comment,$timestamp,$maintenance, $user_id]);


            $stmt = $conn->prepare("
                SELECT asset_records.*, 
                       assets.name AS asset_name,
                       categories.name AS category_name,
                       sub_categories.name AS sub_category_name,
                       room_types.name AS room_type_name,
                       rooms.name AS room_name,
                       persons_in_charge.name AS person_in_charge_name
                FROM asset_records
                JOIN assets ON asset_records.asset_id = assets.id
                JOIN categories ON assets.category_id = categories.id
                JOIN sub_categories ON assets.sub_category_id = sub_categories.id
                JOIN room_types ON asset_records.room_type_id = room_types.id
                JOIN rooms ON asset_records.room_id = rooms.id
                JOIN persons_in_charge ON asset_records.person_in_charge_id = persons_in_charge.id
                WHERE asset_records.id = ?
            ");
            $stmt->execute([$asset_record_id]);
            $updated_asset = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("
                SELECT transfer_history.room_id, 
                       transfer_history.person_in_charge_id, 
                       transfer_history.transferred_date, 
                       transfer_history.transferred_by, 
                       transfer_history.comments,
                       rooms.name AS room_name,
                       persons_in_charge.name AS person_in_charge_name,
                       users.username AS transferred_by_name
                FROM transfer_history
                LEFT JOIN rooms ON transfer_history.room_id = rooms.id
                LEFT JOIN persons_in_charge ON transfer_history.person_in_charge_id = persons_in_charge.id
                LEFT JOIN users ON transfer_history.transferred_by = users.id
                WHERE transfer_history.asset_records_id = ?
                ORDER BY transfer_history.transferred_date DESC
            ");
            $stmt->execute([$asset_record_id]);
            $transfer_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            $qrContent = "Category: " . htmlspecialchars($updated_asset['category_name']) .
                "\nSub-Category: " . htmlspecialchars($updated_asset['sub_category_name']) .
                "\nRoom Type: " . htmlspecialchars($updated_asset['room_type_name']) .
                "\nRoom: " . htmlspecialchars($updated_asset['room_name']) .
                "\nAsset: " . htmlspecialchars($updated_asset['asset_name']) .
                "\nPerson In Charge: " . htmlspecialchars($updated_asset['person_in_charge_name']) .
                "\n\nComment: " . htmlspecialchars($comment) .
                "\nLast Inspected: " . htmlspecialchars($timestamp) .
                "\n\nTransfer History:\n";

            foreach ($transfer_history as $transfer) {
                $qrContent .= "Room: " . htmlspecialchars($transfer['room_name']) .
                    " | Person In Charge: " . htmlspecialchars($transfer['person_in_charge_name']) .
                    " | Transferred Date: " . htmlspecialchars($transfer['transferred_date']) .
                    " | Transferred By: " . htmlspecialchars($transfer['transferred_by_name']) .
                    " | Comments: " . htmlspecialchars($transfer['comments']) . "\n";
            }

            $qrCodeDir = 'qrcodes';
            if (!is_dir($qrCodeDir)) {
                mkdir($qrCodeDir, 0777, true);
            }
            $qrFilePath = $qrCodeDir . '/' . htmlspecialchars($updated_asset['asset_name']) . '_' . $asset_record_id . '.png';
            QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 3);

            $stmt = $conn->prepare("
                UPDATE asset_records 
                SET qrcode = ?
                WHERE id = ?
            ");
            $stmt->execute([$qrFilePath, $asset_record_id]);

            $_SESSION['success_message'] = 'Comment and QR code updated successfully.';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error updating comment: ' . $e->getMessage();
        }
        header('Location: view_assets.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Comment cannot be empty.';
    }
}
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