<?php
session_start();
require 'includes/db.php';
require 'includes/phpqrcode/qrlib.php'; // Include the QR code library

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$error = '';
$success_message = '';
$user_id = $_SESSION['user_id']; // Get the current user's ID

// Fetch username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';

// Fetch data for form options
function fetchOptions($table, $column)
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, $column FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$room_types = fetchOptions('room_types', 'name');
$persons_in_charge = fetchOptions('persons_in_charge', 'name');

// Fetch asset record data
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $asset_record_id = intval($_GET['id']);

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
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$asset) {
        header('Location: view_assets.php');
        exit();
    }
} else {
    header('Location: view_assets.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_type_id = intval($_POST['room_type_id']);
    $room_id = intval($_POST['room_id']);
    $person_in_charge_id = intval($_POST['person_in_charge_id']);
    $timestamp = date('Y-m-d H:i:s');

    if ($room_type_id && $room_id && $person_in_charge_id) {
        try {
            // Insert transfer history with current details
            $stmt = $conn->prepare("
                INSERT INTO transfer_history (asset_records_id, room_id, person_in_charge_id, transferred_date, transferred_by, comments)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$asset_record_id, $asset['room_id'], $asset['person_in_charge_id'], $timestamp, $user_id, $asset['comments']]);

            // Update asset record to reflect new transfer details
            $stmt = $conn->prepare("
                UPDATE asset_records
                SET room_type_id = ?, room_id = ?, person_in_charge_id = ?, comments = NULL, updated_at = ?, last_updated_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$room_type_id, $room_id, $person_in_charge_id, $timestamp, $user_id, $asset_record_id]);

            // Fetch updated asset details and transfer history
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

            // Fetch updated transfer history
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

            // Generate QR code content
            $qrContent = "Category: " . htmlspecialchars($updated_asset['category_name']) .
                "\nSub-Category: " . htmlspecialchars($updated_asset['sub_category_name']) .
                "\nRoom Type: " . htmlspecialchars($updated_asset['room_type_name']) .
                "\nRoom: " . htmlspecialchars($updated_asset['room_name']) .
                "\nAsset: " . htmlspecialchars($updated_asset['asset_name']) .
                "\nPerson In Charge: " . htmlspecialchars($updated_asset['person_in_charge_name']) .
                "\n\nTransfer History:\n";

            foreach ($transfer_history as $transfer) {
                $qrContent .= "Room: " . htmlspecialchars($transfer['room_name']) .
                    " | Person In Charge: " . htmlspecialchars($transfer['person_in_charge_name']) .
                    " | Transferred Date: " . htmlspecialchars($transfer['transferred_date']) .
                    " | Transferred By: " . htmlspecialchars($transfer['transferred_by_name']) .
                    " | Comments: " . htmlspecialchars($transfer['comments']) . "\n";
            }

            // Generate or update the QR code
            $qrCodeDir = 'qrcodes';
            if (!is_dir($qrCodeDir)) {
                mkdir($qrCodeDir, 0777, true);
            }
            $qrFilePath = $qrCodeDir . '/' . htmlspecialchars($updated_asset['asset_name']) . '_' . $asset_record_id . '.png';
            QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_L, 3);

            // Update the asset record with the QR code filename
            $stmt = $conn->prepare("
                UPDATE asset_records 
                SET qrcode = ?
                WHERE id = ?
            ");
            $stmt->execute([$qrFilePath, $asset_record_id]);

            $_SESSION['success_message'] = 'Asset transferred and QR code updated successfully.';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error transferring asset: ' . $e->getMessage();
        }
        header('Location: view_assets.php');
        exit();
    } else {
        $error = 'Please select all required fields.';
    }
}
?>

    <script>
        // Function to update rooms based on selected room type
        function updateRooms() {
            const roomType = document.getElementById('room_type_id').value;
            const roomSelect = document.getElementById('room_id');
            roomSelect.innerHTML = '<option value="">Select Room</option>'; // Clear current options

            if (roomType) {
                fetch('get_rooms.php?room_type=' + roomType)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error); // Log any errors
                        } else {
                            data.forEach(room => {
                                let option = new Option(room.name, room.id);
                                roomSelect.options.add(option);
                            });

                            // Set the selected room if it was previously set
                            const selectedRoomId = <?php echo json_encode($asset['room_id']); ?>;
                            if (selectedRoomId) {
                                roomSelect.value = selectedRoomId;
                            }
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }
        }

        // Function to confirm asset transfer
        function confirmTransfer(event) {
            event.preventDefault(); // Prevent form from submitting immediately

            const confirmAction = confirm("Are you sure you want to transfer this asset?");
            if (confirmAction) {
                event.target.submit(); // If confirmed, submit the form
            }
        }

        // Add event listener to the room type dropdown
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('room_type_id').addEventListener('change', updateRooms);
            document.getElementById('transfer-form').addEventListener('submit', confirmTransfer);

            // Set the selected room type
            const selectedRoomTypeId = <?php echo json_encode($asset['room_type_id']); ?>;
            if (selectedRoomTypeId) {
                document.getElementById('room_type_id').value = selectedRoomTypeId;
                updateRooms(); // Update room options based on selected room type
            }


            const selectedPersonInChargeId = <?php echo json_encode($asset['person_in_charge_id']); ?>;
            if (selectedPersonInChargeId) {
                document.getElementById('person_in_charge_id').value = selectedPersonInChargeId;
            }
        });

        function displaySuccessMessage() {
            const successMessage = "<?php echo isset($_SESSION['success_message']) ? htmlspecialchars($_SESSION['success_message']) : ''; ?>";
            if (successMessage) {
                alert(successMessage);
                <?php unset($_SESSION['success_message']); ?>
            }
        }
    </script>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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
      

    
    <div class="flex-1
     flex flex-col items-center justify-center ml-10 top-0">
        <div class="flex justify-center items-center h-screen ">
    <div class="max-w-2xl w-full bg-white shadow-lg rounded-lg p-6">
        <div class="flex">
            <!-- Left Side -->
            <form id="transfer-form" action="transfer_asset.php?id=<?php echo $asset_record_id; ?>" method="POST">
            <div class="flex-1">
                <h2 class="text-xl font-semibold mb-4">Transfer Asset</h2>
                
                <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Select Room:</label>

                <select id="room_type_id" name="room_type_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Select Room Type</option>
                <?php foreach ($room_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type['id']); ?>" <?php echo ($type['id'] == $asset['room_type_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
                </div>
                
                <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Specific Room:</label>
                <select id="room_id" name="room_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="room_id" required>
                <option value="">Select Room</option>
            </select>
                  
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Person-in-Charge:</label>
                    <select id="person_in_charge_id" name="person_in_charge_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">Select Person In Charge</option>
                <?php foreach ($persons_in_charge as $person): ?>
                    <option value="<?php echo htmlspecialchars($person['id']); ?>" <?php echo ($person['id'] == $asset['person_in_charge_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($person['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-start">
                    <button class="bg-blue-600 text-white py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">Submit</button>
                    <a href="view_assets.php" class="block text-center mt-2.5 ml-3 text-black">Go Back</a>
                </div>
            </div>
            </form>
            <?php 
          $query = $conn->prepare("
          SELECT 
              th.room_id, 
              r.name as room_name,
              r.room_type_id,
              th.person_in_charge_id, 
              p.name as person_in_charge_name, 
              th.transferred_date, 
              th.transferred_by, 
              th.comments, 
              u.username as transferred_by_name
          FROM 
              transfer_history th
          JOIN 
              rooms r ON th.room_id = r.id
          JOIN 
              persons_in_charge p ON th.person_in_charge_id = p.id
          JOIN 
              users u ON th.transferred_by = u.id
          WHERE 
              th.asset_records_id = ? ORDER BY th.transferred_date LIMIT 3
      ");
      $query->execute([$asset_record_id]);
      
      $transfer_data = $query->fetchAll(PDO::FETCH_ASSOC);
      

            ?>
 <div class="ml-6 w-82 bg-blue-600 text-white p-4 rounded-lg">
    <h3 class="text-sm font-bold mb-4">Transfer History</h3>
    <?php foreach ($transfer_data as $data): ?>
        <div class="bg-gray-300 text-gray-700 w-full py-4 px-6 mb-2 rounded-lg">
            <p class="font-semibold text-sm">Room: <?php echo htmlspecialchars($data['room_name']); ?> (Room Type ID: <?php echo htmlspecialchars($data['room_type_id']); ?>)</p>
            <p class="font-semibold text-sm">Person in Charge: <?php echo htmlspecialchars($data['person_in_charge_name']); ?></p>
            <p>Comments: <?php echo htmlspecialchars($data['comments']); ?></p>
            <p class="text-xs text-right text-sm">Transferred by: <?php echo htmlspecialchars($data['transferred_by_name']); ?> on <?php echo htmlspecialchars($data['transferred_date']); ?></p>
        </div>
    <?php endforeach; ?>
    <p class="text-xs mt-2 text-right">See more, refresh.</p>
</div>


        </div>
    </div>
</div>

      
    </div>

</body>

</html>
