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

// Fetch all categories
$categoryQuery = $conn->query("SELECT DISTINCT c.name FROM asset_records ar JOIN categories c ON ar.category_id = c.id;");
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch asset data for the chart (all categories by default)
$chartQuery = $conn->query("SELECT 
    c.name AS category_name, 
    COALESCE(AVG(ABS(DATEDIFF(pr.date, ar.disposal_date))), 0) AS avg_durability
FROM 
    categories c
LEFT JOIN asset_records ar ON ar.category_id = c.id
LEFT JOIN procurement_requests pr ON ar.person_in_charge_id = pr.person_in_charge_id
    AND pr.status = 'Approved'
GROUP BY 
    c.name;");
$chartData = $chartQuery->fetchAll(PDO::FETCH_ASSOC);


$assetQuery = $conn->query("SELECT ar.id, c.name AS category_name FROM asset_records ar 
                            JOIN categories c ON ar.category_id = c.id
                            GROUP BY ar.id;");
$assetList = $assetQuery->fetchAll(PDO::FETCH_ASSOC);
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
                <a href="dashboard.php">
                    <button class="nav-icon fas fa-tachometer-alt text-white text-sm"> Dashboard</button>             
                </a>
                <a href="view_assets.php">
                    <button class="nav-icon fas fa-folder text-white text-sm"> View Assets</button>
                </a>
                <a href="dispose_assets.php">
                    <button class="nav-icon fas fa-folder text-white text-sm"> View Disposed Assets</button>
                </a>
                <a href="logout.php">
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
</script>

    <div class="ml-64 p-4 flex space-x-4">
        <!-- Graph Section -->
        <div class="bg-white p-6 rounded-xl shadow-lg w-2/3">
            <h2 class="text-lg font-semibold mb-4">Asset Durability Chart</h2>
            <!-- Dropdown for category selection -->
            <div class="mb-4 text-right">
                <label for="categoryDropdown" class="text-sm text-gray-700" hidden>Select Category:</label>
                <select id="categoryDropdown" class="ml-2 p-2 border rounded-md" hidden>
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['name']); ?>"><?= htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Graph -->
            <div id="chart_div" style="width: 900px; height: 500px;"></div>
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
                        <?php foreach ($assetList as $asset): ?>
                            <tr>
                                <td class="border px-4 py-2"><?= htmlspecialchars($asset['id']) ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($asset['category_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
       google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    var categoryFilter = document.getElementById('categoryDropdown').value;
    var data = google.visualization.arrayToDataTable([
        ['Category', 'Average Durability (Days)'],
        <?php
        
        foreach ($chartData as $chart) {
            echo "['" . $chart['category_name'] . "', " . $chart['avg_durability'] . "],";
        }
        ?>
    ]);

    var options = {
        title: 'Asset Durability by Category',
        chartArea: {width: '65%', height: '80%'}, 
        hAxis: {
            title: 'Average Durability (Days)',
            minValue: 0
        },
        vAxis: {
            title: 'Category'
        },
        tooltip: {
            trigger: 'selection',
            isHtml: true,
            textStyle: {color: '#000000'},
            showColorCode: true
        },
        animation: {
            startup: true,
            easing: 'inAndOut',
            duration: 1000
        },
        legend: { position: 'none' }, 
        width: '100%',  
        height: '100%'  
    };

    var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
    chart.draw(data, options);
}


document.getElementById('categoryDropdown').addEventListener('change', function () {
    drawChart();
});


window.addEventListener('resize', function() {
    drawChart();
});
    </script>
</body>
</html>
