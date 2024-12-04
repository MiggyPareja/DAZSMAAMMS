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
$stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['email']) : 'Unknown User';
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

<body class="bg-cover bg-center h-screen bg-primary">
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
                        <a href="Records/view_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Assets</button>
                        </a>
                        <?php if ($role == 'Admin' || $role == 'Property Custodian'): ?>
                        <a href="Records/dispose_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Disposed Assets</button>
                        </a>
                        <a href="Records/add_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Deploy Assets</button>
                        </a>
                        <?php endif; ?>
                        <a href="Records/view_request.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Requests</button>
                        </a>
                        <a href="Records/procurement.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Generate Request</button>
                        </a>
                    </div>
                    
                    <!-- Reports -->
                    <a>
                    <button id="reportsBtn" class="nav-icon fas fa-chart-bar text-white text-sm"> Reports
    <span id="arrow" class="transform transition-transform">&#9660;</span>
</button></a>
                    <div id="reportsMenu" class="hidden flex flex-col p-2 space-y-3">
                        <a href="Reports/reports.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Person-In-Charge</button>
                        </a>
                        <a href="Reports/asset_durability.php">
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
                    <a href="Records/procurement.php">
                        <button class="far fa-circle nav-icon text-white text-sm"> Generate Request</button>
                    </a>
                    <a href="logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
      
        <?php if ($role == 'Faculty'): ?>
            <div class="flex-1 flex flex-col items-center ml- justify-start ml-72 mt-10 ">

            <div class="grid grid-cols-3 gap-4 mb-4">
  <div class="top-0">


  </div>
  </div>
  </div>
            <?php elseif ($role == 'Requestor' || $role == 'Admin' || $role == 'Property Custodian' || $role == 'Inspector'): ?> 
                <div class="flex-1 flex flex-col items-center justify-center ml-64">
  
        
  
  <div class="top-0">
  <h3 class="text-xl font-semibold mb-4 text-white text-left mt-4">Dashboard</h3>
  </div>

  <div class="grid grid-cols-3 gap-4 mb-4">
      <div class="bg-white shadow rounded-xl p-6 w-64 text-left">
          <?php $totalAssetsQuery = "SELECT COUNT(*) as total FROM assets WHERE 1";
          $stmt = $conn->query($totalAssetsQuery);
          $totalAssets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

          $totalRequestsQuery = "SELECT COUNT(*) as total FROM procurement_requests WHERE status = 'pending'";
          $stmt = $conn->query($totalRequestsQuery);
          $totalRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

          $assignedAssetsQuery = "SELECT COUNT(*) as total FROM assets ";
          $stmt = $conn->query($assignedAssetsQuery);
          $assignedAssets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


          ?>
          <div class="text-3xl font-bold"><?php echo $totalAssets; ?></div>
          <div class="text-gray-500">Assets</div>
      </div>
      <div class="bg-white shadow rounded-xl p-6 w-64 text-left">
<div class="text-3xl font-bold"><?php echo $assignedAssets; ?></div>
<div class="text-gray-500">Assigned Assets</div>
<button id="viewUnassignBtn" class="text-blue-500 mt-4 cursor-pointer">View Unassign</button>


</div>


<script>
$(document).ready(function() {
  $('#viewUnassignBtn').on('click', function() {
      // Toggle the hidden class on the container
      $('#unassignContainer').toggleClass('hidden');

      // Check if the container is now visible (i.e., the class "hidden" is removed)
      if (!$('#unassignContainer').hasClass('hidden')) {
          $.ajax({
              url: 'fetch_assets.php', 
              method: 'GET',
              success: function(data) {
                  const assets = JSON.parse(data);
                  $('#assetTableBody').empty();
                  assets.forEach(function(asset) {
                      $('#assetTableBody').append(`
                          <tr>
                              <td class="py-2 px-4 border-b">${asset.name}</td>
                              <td class="py-2 px-4 border-b">${asset.assetCount}</td>
                          </tr>
                      `);
                  });
              },
              error: function() {
                  alert('Failed to fetch data.');
              }
          });
      }
  });
});

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


      <div class="bg-white shadow rounded-xl p-6 w-64 text-left">
          <div class="text-3xl font-bold"><?php echo $totalRequests; ?></div>
          <div class="text-gray-500">Request</div>
      </div>
  </div>

  <div class="grid grid-cols-2 gap-8">
      <?php
      $sql = "SELECT u.username, u.role, l.activity, l.userID, l.id 
FROM users
JOIN users u ON u.id = l.userID";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


      $groupedLogs = [];
      foreach ($logs as $log) {
          $groupedLogs[$log['userID']]['username'] = $log['username'];
          $groupedLogs[$log['userID']]['role'] = $log['role'];
          $groupedLogs[$log['userID']]['activities'][] = $log['activity'];
      }
      ?>
<div id="unassignContainer" class="bg-white shadow rounded-xl p-6 w-full mt-4 hidden">
<h2 class="text-2xl font-bold mb-4">Unassigned Assets</h2>
<table class="min-w-full bg-white">
  <thead>
      <tr>
          <th class="py-2 px-4 border-b">Asset Name</th>
          <th class="py-2 px-4 border-b">Total Count</th>
      </tr>
  </thead>
  <tbody id="assetTableBody">
  </tbody>
</table>
</div>

<div class="bg-white shadow rounded-xl p-6">
<h4 class="text-lg font-semibold w-72 mb-4">System Activity</h4>

<?php foreach ($groupedLogs as $userID => $userData): ?>
  <div class="mb-4">
      <div class="flex justify-between items-center">
          <div class="font-bold"><?= htmlspecialchars($userData['role']) ?></div>
          <button class="text-blue-500" onclick="toggleActivity(<?= $userID ?>)">See Activity</button>
      </div>
      <div><?= htmlspecialchars($userData['username']) ?></div>
  </div>

  <!-- Hidden activity container -->
  <div id="activity-container-<?= $userID ?>" class="activity-container bg-gray-100 p-4 mt-4 rounded-lg" style="display: none;">
      <h5 class="font-bold mb-2">Activities for <?= htmlspecialchars($userData['username']) ?>:</h5>
      <ul>
          <?php foreach ($userData['activities'] as $activity): ?>
              <li><?= htmlspecialchars($activity) ?></li>
          <?php endforeach; ?>
      </ul>
  </div>
<?php endforeach; ?>
</div>

<script>
function toggleActivity(userID) {
  var container = document.getElementById('activity-container-' + userID);
  if (container.style.display === 'none') {
      container.style.display = 'block';
  } else {
      container.style.display = 'none';
  }
    }
</script>
      <div class="bg-white shadow rounded-xl p-6">
      <a href="data_analytics.php"><button class="bg-blue-600 hover:bg-blue-700 w-full text-white font-bold py-2 rounded-2xl">View Analytic</button>
      </a>
      <?php $query = "
  SELECT 
      s.name AS subcategory_name,
      COUNT(a.id) AS asset_count,
      (COUNT(a.id) / total_assets.total_count) * 100 AS percentage
  FROM 
      assets a
  JOIN 
      sub_categories s ON a.sub_category_id = s.id
  JOIN 
      (SELECT COUNT(*) as total_count FROM assets) total_assets
  GROUP BY 
      s.name
";

      $stmt = $conn->query($query);
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <div class="mb-4">
          <div class="text-gray-600">Statistic</div>
              <div class="mt-4">
                  <?php foreach ($results as $row): ?>
                  <div class="flex justify-between">
                  <div><?php echo htmlspecialchars($row['subcategory_name']); ?></div>
                  <div><?php echo round($row['percentage'], 2); ?>%</div>
          </div>
          <?php endforeach; ?>
          </div>
      </div>
      <?php
      $sql = "SELECT SUM(amount) AS total_amount FROM  procurement_requests WHERE status ='Approved'";
      $stmt = $conn->query($sql);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $totalAmount = number_format($row['total_amount'], 2, '.', ',');
      ?>
      </div>
  
  </div>
  </div>
        
        <div class="w-46 flex flex-col p-4 space-y-4 z-20">
           
            <img src="images/DAZSMALOGO.png" alt="School Logo" class="w-48 h-48">

        </div>

        <?php endif; ?>
      
    </div>


    <div class="absolute bottom-0 left-0 right-0 flex justify-center space-x-8 p-4">
        <a href="#" class="text-sm font-bold text-white">HELP</a>
    </div>
</body>

</html>