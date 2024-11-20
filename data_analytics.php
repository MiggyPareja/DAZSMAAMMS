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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.tailwindcss.com"></script>

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
        <div class="p-8 top-0">
        <h3 class="text-xl font-semibold mb-4 text-white mt-4">Data Anaytics</h3>
        </div>
            <div class="flex space-x-8">
       
                <div class="bg-white rounded-2xl shadow-lg p-4 w-96">
                    <h2 class="text-lg font-bold mb-2">Asset Requests</h2>
                    <div id="chart"></div>
                </div>

      
                <div class="bg-white rounded-2xl shadow-lg p-4 w-96">
                    <h2 class="text-lg font-bold mb-2">Asset Allocation</h2>
                    <div id="allocation"></div>
                    <a href="dashboard.php" class="block text-center mt-4 text-blue">Go Back</a>
                </div>
            </div>
        </div>
        

      
    </div>


    <div class="absolute bottom-0 left-0 right-0 flex justify-center space-x-8 p-4">
        <a href="#" class="text-sm font-bold text-white">HELP</a>
    </div>
</body>
<?php 

$totalQuery = $conn->query("SELECT SUM(assetCount) AS totalAssets FROM assets");
$totalResult = $totalQuery->fetch(PDO::FETCH_ASSOC);
$totalAssets = $totalResult['totalAssets'];


$assignedQuery = $conn->query("SELECT COUNT(*) AS assignedAssets FROM asset_records");
$assignedResult = $assignedQuery->fetch(PDO::FETCH_ASSOC);
$assignedAssets = $assignedResult['assignedAssets'];

$percentageAssigned = ($assignedAssets / $totalAssets) * 100;


?>
<?php   $query = "
        SELECT 
            MONTHNAME(date) as month, 
            COUNT(id) as request_count
        FROM 
            procurement_requests
        WHERE 
            status = 'pending' -- Filter by status if needed
        GROUP BY 
            MONTH(date)
        ORDER BY 
            MONTH(date)
    ";
    
    $stmt = $conn->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $months = [];
    $requestCounts = [];

    foreach ($results as $row) {
        $months[] = $row['month'];
        $requestCounts[] = $row['request_count'];
    }
    $monthsJson = json_encode($months);
    $requestCountsJson = json_encode($requestCounts);
 ?>
<script>
    
var options = {
    series: [{
        name: "Asset Requests",
        data: <?php echo $requestCountsJson; ?> // PHP variable injected here
    }],
    chart: {
        height: 350,
        type: 'line',
        zoom: {
            enabled: false
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'straight'
    },
    title: {
        text: 'Asset Requests by Month',
        align: 'left'
    },
    grid: {
        row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
        },
    },
    xaxis: {
        categories: <?php echo $monthsJson; ?>, // PHP variable injected here
    }
};

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();


    var totalAssets = <?php echo $totalAssets; ?>;
    var assignedAssets = <?php echo $assignedAssets; ?>;
    var percentageAssigned = <?php echo $percentageAssigned; ?>;

    var options = {
        series: [percentageAssigned],
        chart: {
            type: 'radialBar',
            offsetY: -20,
            sparkline: {
                enabled: true
            }
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                track: {
                    background: "#e7e7e7",
                    strokeWidth: '97%',
                    margin: 5, 
                    dropShadow: {
                        enabled: true,
                        top: 2,
                        left: 0,
                        color: '#999',
                        opacity: 1,
                        blur: 2
                    }
                },
                dataLabels: {
                    name: {
                        show: false
                    },
                    value: {
                        offsetY: -2,
                        fontSize: '22px'
                    }
                }
            }
        },
        grid: {
            padding: {
                top: -10
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                shadeIntensity: 0.4,
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 50, 53, 91]
            },
        },
        labels: ['Assigned Assets'],
    };

    var chart = new ApexCharts(document.querySelector("#allocation"), options);
    chart.render();
</script>

</html>
