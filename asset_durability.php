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
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';

// Fetch asset data from the database
$stmt = $conn->query("SELECT * FROM asset_records ar JOIN categories c ON ar.category_id = c.id JOIN sub_categories sc ON ar.sub_category_id = sc.id JOIN room_types rt ON ar.room_type_id = rt.id JOIN rooms r ON ar.room_id = r.id JOIN persons_in_charge pic ON ar.person_in_charge_id = pic.id;
");
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categoryQuery = $conn->query("SELECT name FROM categories;");
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | DAZSMA AMMS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

    <!-- Main Content -->
    <div class="ml-64 p-4 flex space-x-4">
        <!-- Graph Section -->
        <div class="bg-white p-6 rounded-xl shadow-lg w-2/3">
            <h2 class="text-lg font-semibold mb-4">Asset Durability Chart</h2>
            <!-- Dropdown for category selection -->
            <div class="mb-4 text-right">
                <label for="categoryDropdown" class="text-sm text-gray-700">Select Category:</label>
                <select id="categoryDropdown" class="ml-2 p-2 border rounded-md">
                    <option value="all">All Categories</option>
                    <?php
                    // Extract unique category names
                    $category_names = array_unique(array_column($categories, 'name'));
                    foreach ($category_names as $category) {
                        echo "<option value='$category'>$category</option>";
                    }
                    ?>
                </select>
            </div>
            <!-- Placeholder for Graph -->
            <canvas id="durabilityChart" width="400" height="200"></canvas>
        </div>

        <!-- Asset List Section -->
        <div class="bg-white p-6 rounded-xl shadow-lg w-1/3">
            <h2 class="text-lg font-semibold mb-4">Assets List</h2>
            <div class="overflow-y-auto" style="max-height: 300px;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border px-4 py-2">Asset ID</th>
                            <th class="border px-4 py-2">Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="border px-4 py-2"><?= htmlspecialchars($category['asset_id']) ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($category['name']) ?></td> <!-- Update as needed -->
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('durabilityChart').getContext('2d');
        
        // Calculate days between last_inspected and disposal_date
        const assets = <?php echo json_encode($assets); ?>;
        
        const labels = assets.map(asset => asset.category_id);
        const data = assets.map(asset => {
            const dateAdded = new Date(asset.last_inspected);
            const disposalDate = asset.disposal_date ? new Date(asset.disposal_date) : new Date();
            const diffTime = Math.abs(disposalDate - dateAdded);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays;
        });

        const durabilityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Days from Date Added to Disposal',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Dropdown change event to filter the chart by category
        document.getElementById('categoryDropdown').addEventListener('change', function() {
            const selectedCategory = this.value;
            const filteredAssets = selectedCategory === 'all' ? assets : assets.filter(asset => asset.category_id === selectedCategory);
            
            const filteredLabels = filteredAssets.map(asset => asset.category_id);
            const filteredData = filteredAssets.map(asset => {
                const dateAdded = new Date(asset.last_inspected);
                const disposalDate = asset.disposal_date ? new Date(asset.disposal_date) : new Date();
                const diffTime = Math.abs(disposalDate - dateAdded);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays;
            });

            durabilityChart.data.labels = filteredLabels;
            durabilityChart.data.datasets[0].data = filteredData;
            durabilityChart.update();
        });
    </script> -->
</body>
</html>
