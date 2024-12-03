<?php
// view_request.php

session_start();
require '../includes/db.php'; // Include DB connection

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
// Fetch all procurement requests with the person in charge name
try {
    $sql = "SELECT pr.*, pic.name AS requested_by_name 
            FROM procurement_requests pr
            LEFT JOIN persons_in_charge pic ON pr.person_in_charge_id = pic.id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    $sql = "SELECT th.*, 
                   pic.name AS person_in_charge_name, 
                   r.name AS room_name
            FROM transfer_history th
            LEFT JOIN persons_in_charge pic ON th.person_in_charge_id = pic.id
            LEFT JOIN rooms r ON th.room_id = r.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $transferHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#requestsTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
        });
    });
</script>


<script>
    $(document).ready(function() {
        $('#requestsTables').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
        });
    });
</script>



<script>
        function confirmAction(message) {
            return confirm(message);
        }
    </script>
<body class="bg-cover bg-center h-screen"
    style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('../images/Background.png');">
        <a>
        <div class="bg-blue-900 w-64 flex flex-col p-4 fixed h-full space-y-4 z-20">
              <div class="image">
                  <img src="../images/SYSTEM LOGO 2.png" alt="User Image" class="text-white text-left">
              </div>
        </a>



        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
              <div class="image">
                  <img src="../images/avatar.png" class="rounded-full w-12 h-12" alt="User Image">
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
                        <a href="../manage_users.php">
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
      

    
        <div class="flex-1 flex flex-col items-start ml-64">
  
<div class="assets-table mt-8 p-4 rounded-lg bg-white">


<h1 class="text-2xl font-bold mb-4">Procurement Requests Dashboard</h1>


<?php if (isset($_GET['success'])): ?>
    <p class="text-green-500 mb-4"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p class="text-red-500 mb-4"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
<table id="requestsTable" class="min-w-full bg-white border border-gray-200">
    <thead>
        <tr>
            <th class="px-4 py-2 border-b">Department</th>
            <th class="px-4 py-2 border-b">Date</th>
            <th class="px-4 py-2 border-b">Requested by</th>
            <th class="px-4 py-2 border-b">Particular Asset</th>
            <th class="px-4 py-2 border-b">Quantity</th>
            <th class="px-4 py-2 border-b">Unit Cost</th>
            <th class="px-4 py-2 border-b">Amount</th>
            <th class="px-4 py-2 border-b">Status</th>
            <th class="px-4 py-2 border-b">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($requests)): ?>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['department']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['date']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['requested_by_name']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['particular_asset']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['quantity']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['unit_cost']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['amount']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($request['status']); ?></td>
                    <td class="px-4 py-2 border-b">
                    <div class="flex space-x-2">
                        <?php if ($role == 'Admin' && $request['status'] == 'Pending'): ?>
                            <form action="process_request.php" method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="action" value="approve" onclick="return confirmAction('Are you sure you want to approve this request?')" class="text-white bg-green-500 hover:bg-green-700 px-2 py-1 rounded">Approve</button>
                            </form>
                            <form action="process_request.php" method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="action" value="deny" onclick="return confirmAction('Are you sure you want to deny this request?')" class="text-white bg-red-500 hover:bg-red-700 px-2 py-1 rounded">Deny</button>
                            </form>
                        <?php elseif ($role == 'Admin' && $request['status'] == 'Approved'): ?>
                            <form action="generate_pdf.php" method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?php echo $request['person_in_charge_id']; ?>">
                                <button type="submit" class="text-white bg-blue-500 hover:bg-blue-700 px-2 py-1 rounded">Generate PDF</button>
                            </form>

                        <?php elseif ($role == 'Requestor' && $request['status'] == 'Approved'): ?>
                            <form action="generate_pdf.php" method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" class="text-white bg-blue-500 hover:bg-blue-700 px-2 py-1 rounded">Generate PDF</button>
                            </form>
                        <?php endif; ?>
                    </td>
                  
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="px-4 py-2 border-b text-center">No procurement requests found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

      
    </div>
    <div class="assets-table mt-8 p-4 rounded-lg ml-3 bg-white">


<h1 class="text-2xl font-bold mb-4">Transfer Requests Dashboard</h1>

<?php if (isset($_GET['success'])): ?>
    <p class="text-green-500 mb-4"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p class="text-red-500 mb-4"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<table id="requestsTables" class="min-w-full bg-white border border-gray-200">
    <thead>
        <tr>
            <th class="px-4 py-2 border-b">Asset</th>
            <th class="px-4 py-2 border-b">Room</th>
            <th class="px-4 py-2 border-b">Person in Charge</th>
            <th class="px-4 py-2 border-b">Date Transferred</th>
            <th class="px-4 py-2 border-b">Comments</th>
            <th class="px-4 py-2 border-b">status</th>


            <th class="px-4 py-2 border-b">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($transferHistory)): ?>
            <?php foreach ($transferHistory as $transferHistory): ?>
                <tr>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($transferHistory['asset_records_id']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($transferHistory['room_name']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($transferHistory['person_in_charge_name']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($transferHistory['transferred_date']); ?></td>
 
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($transferHistory['comments']); ?></td>
                    <td class="px-4 py-2 border-b"><?php echo htmlspecialchars($transferHistory['status']); ?></td>

                    <td class="px-4 py-2 border-b">
                    <div class="flex space-x-2">
                        <?php if ($role == 'Admin' && $transferHistory['status'] == 'Pending'): ?>
                            <form action="approve_request.php" method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?php echo $transferHistory['id']; ?>">
                                <button type="submit" name="action" value="approve" onclick="return confirmAction('Are you sure you want to approve this request?')" class="text-white bg-green-500 hover:bg-green-700 px-2 py-1 rounded">Approve</button>
                            </form>
                            <form action="denied_request.php" method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?php echo $transferHistory['id']; ?>">
                                <button type="submit" name="action" value="deny" onclick="return confirmAction('Are you sure you want to deny this request?')" class="text-white bg-red-500 hover:bg-red-700 px-2 py-1 rounded">Deny</button>
                            </form>
                            <?php elseif ($role == 'Admin' && $transferHistory['status'] == 'Approved'): ?>
                               
                                <form action="generate transfer.php" method="POST" class="inline">
                                <input type="hidden" name="transfer" value="<?php echo $transferHistory['id']; ?>">
                                <button type="submit" class="text-white bg-blue-500 hover:bg-blue-700 px-2 py-1 rounded">Generate PDF</button>
                            </form>
                        <?php endif; ?>
                    </td>
                  
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="px-4 py-2 border-b text-center">No procurement requests found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

      
    </div>
    </div>

    </div>  
</body>


</html>
