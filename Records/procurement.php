<?php
// procurement.php

session_start();
require '../includes/db.php'; // Include DB connection

// Fetch options for "Requested by" dropdown
function fetchPersonsInCharge()
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$persons_in_charge = fetchPersonsInCharge();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = $_POST['department'] ?? '';
    $date = $_POST['date'] ?? '';
    $personInChargeId = $_POST['requestedBy'] ?? '';
    $particularAsset = $_POST['particularAsset'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $price = $_POST['price'];
    $model = $_POST['model'];
    $specs = $_POST['specs'];

    $amount = $quantity * $price;

    try {
        // Insert form data into the database using PDO
        $sql = "INSERT INTO procurement_requests (department, request_date, person_in_charge_id, particular_asset, quantity, unit_cost, status, model, specs, amount) 
                VALUES (:department, :date, :personInChargeId, :particularAsset, :quantity, :price, 'Pending', :model, :specs, :amount)";
        $stmt = $conn->prepare($sql);

        // Bind the parameters to the query
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':personInChargeId', $personInChargeId);
        $stmt->bindParam(':particularAsset', $particularAsset);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':specs', $specs);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':price', $price);
        // Execute the query
        $stmt->execute();
      

        // Redirect to view_request.php after successful submission
        header('Location: procurement.php?success=Procurement request sent');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form</title>
    <script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
          @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

* {
    font-family: 'Poppins', sans-serif;
}
</style>
    <script>
        function confirmSubmission(event) {
            if (!confirm("Are you sure you want to submit this request?")) {
                event.preventDefault(); 
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', confirmSubmission);
        });
    </script>
</head>

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
      

    
        <div class="flex-1 flex flex-col items-center justify-center ml-64 top-0">
  

    <div class="bg-white p-4 rounded-lg shadow-lg w-full max-w-lg">
        <h1 class="text-xl font-bold mb-6 text-center">Request Form</h1>
        <form action="procurement.php" method="POST" class="space-y-2">
        <?php if (isset($_GET['error'])) { ?>
    <div id="alert-2" class="error flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
  <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
  </svg>
  <span class="sr-only">Info</span>
  <div class="ms-3 text-sm font-medium">
  <?php echo $_GET['error']; ?>
  </div>
  <button id="closeErrorBtn" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-2" aria-label="Close">
    <span class="sr-only">Close</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
    </svg>
  </button>
</div>      
<script>
   
    const closeErrorBtn = document.getElementById('closeErrorBtn');
    const errorContainer = document.querySelector('.error');

    closeErrorBtn.addEventListener('click', () => {
        errorContainer.style.display = 'none'; 
       
        const url = new URL(window.location.href);
        url.searchParams.delete('error');
        history.replaceState({}, document.title, url);
    });

    setTimeout(() => {
        errorContainer.style.display = 'none';

        const url = new URL(window.location.href);
        url.searchParams.delete('error');
        history.replaceState({}, document.title, url);
    }, 7000); 
</script>
     	           <?php } ?>
                  <?php if (isset($_GET['success'])) { ?>
                    <div id="alert-1" class="success flex items-center p-4 mb-4 text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
  <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
  </svg>
  <span class="sr-only">Info</span>
  <div class="ms-3 text-sm font-medium">
  <?php echo $_GET['success']; ?>
  </div>
    <button id="closeSuccessBtn" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-1" aria-label="Close">
      <span class="sr-only">Close</span>
      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
      </svg>
  </button>
</div>   
<script>
    const closesuccessBtn = document.getElementById('closeSuccessBtn');
    const successContainer = document.querySelector('.success');

    closesuccessBtn.addEventListener('click', () => {
        successContainer.style.display = 'none';
        
        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        history.replaceState({}, document.title, url);
    });


    setTimeout(() => {
        successContainer.style.display = 'none';

        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        history.replaceState({}, document.title, url);
    }, 7000); 
</script>
     	           <?php } ?>
            <div>
                <label for="department" class="block text-gray-700 font-medium">Department (Optional):</label>
                <input type="text" id="department" name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="date" class="block text-gray-700 font-medium">Date:</label>
                <input type="date" id="date" name="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="requestedBy" class="block text-gray-700 font-medium">Requested by:</label>
                <select id="requestedBy" name="requestedBy" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select Person In Charge</option>
                    <?php foreach ($persons_in_charge as $person): ?>
                        <option value="<?php echo htmlspecialchars($person['user_id']); ?>">
                        <?php echo htmlspecialchars($person['last_name'] .',' . $person['first_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
           
         
            <div class="mb-1 flex w-full">
        <div class="mb-1 w-full mr-2">
        <label for="particularAsset" class="block text-gray-700 font-medium">Brand:</label>
        <input placeholder="Enter Brand" type="text" id="particularAsset" name="particularAsset" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-1 w-full">
        <label for="particularAsset" class="block text-gray-700 font-medium">Model:</label>
                <input placeholder="Enter Model" type="text" name="model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
           
        </div>

        </div>
        
        <div class="mb-1 flex w-full">
        <div class="mb-1 w-full mr-2">
        <label for="quantity" class="block text-gray-700 font-medium">Quantity:</label>
        <input type="number" id="quantity" name="quantity" min="1" step="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>      </div>
      
        <div class="mb-1 w-full">
        <label for="particularAsset" class="block text-gray-700 font-medium">Price:</label>
                <input placeholder="Enter Price" type="text"  name="price" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
           
        </div>

        </div>
            <div>
                <label for="particularAsset" class="block text-gray-700 font-medium">Specs:</label>
                <input placeholder="Enter Specs" type="text" id="particularAsset" name="specs" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="w-full py-2 bg-blue-500 text-white font-bold rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Submit Form</button>
        </form>
    </div>

      
    </div>


    <div class="absolute bottom-0 left-0 right-0 flex justify-start ml-64 space-x-8 p-4">
        <a href="#" class="text-sm font-bold text-white">HELP</a>
    </div>
</body>

</html>
