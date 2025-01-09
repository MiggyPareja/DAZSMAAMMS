<?php
require './includes/db.php';
require './sidebar.php';

$idNumber = $user ? htmlspecialchars($user['id_number']) : 'Unknown User';
$query = "SELECT brands.brand_name as brandName ,models.model_name as modelName,inv.*
    FROM inventory inv
    JOIN models on models.model_id = inv.model_id
    JOIN brands on brands.brand_id = inv.brand_id
    JOIN users on users.user_id = inv.requested_by
    WHERE inv.deployed_to = :idNumber";

$stmt = $conn->prepare($query); // Prepare the SQL statement
$stmt->bindValue(':idNumber', $idNumber, PDO::PARAM_STR); // Bind the id_number parameter
$stmt->execute(); // Execute the query

// Fetch the data
$deployedToUser = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to count total assets
$totalAssetsQuery = "SELECT COUNT(*) as total FROM inventory";
$totalAssetsStmt = $conn->prepare($totalAssetsQuery);
$totalAssetsStmt->execute();
$totalAssets = $totalAssetsStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Query to count deployed assets
$deployedAssetsQuery = "SELECT COUNT(*) as deployed FROM inventory WHERE status = 'Deployed'";
$deployedAssetsStmt = $conn->prepare($deployedAssetsQuery);
$deployedAssetsStmt->execute();
$deployedAssets = $deployedAssetsStmt->fetch(PDO::FETCH_ASSOC)['deployed'];

// Query to count disposed assets
$disposedAssetsQuery = "SELECT COUNT(*) as disposed FROM inventory WHERE status = 'Disposed'";
$disposedAssetsStmt = $conn->prepare($disposedAssetsQuery);
$disposedAssetsStmt->execute();
$disposedAssets = $disposedAssetsStmt->fetch(PDO::FETCH_ASSOC)['disposed'];

$pendingRequestsQuery = "SELECT COUNT(*) as pending FROM inventory WHERE status = 'Pending'";
$pendingRequestsStmt = $conn->prepare($pendingRequestsQuery);
$pendingRequestsStmt->execute();
$pendingRequests = $pendingRequestsStmt->fetch(PDO::FETCH_ASSOC)['pending'];

$approvedRequestsQuery = "SELECT COUNT(*) as approved FROM inventory WHERE status = 'Approved'";
$approvedRequestsStmt = $conn->prepare($approvedRequestsQuery);
$approvedRequestsStmt->execute();
$approvedRequests = $approvedRequestsStmt->fetch(PDO::FETCH_ASSOC)['approved'];


$disposedRequestsQuery = "SELECT COUNT(*) as disposed FROM inventory WHERE status = 'Disposed'";
$disposedRequestsStmt = $conn->prepare($disposedRequestsQuery);
$disposedRequestsStmt->execute();
$disposedRequests = $disposedRequestsStmt->fetch(PDO::FETCH_ASSOC)['disposed'];



// Calculate durability and predictive analysis
$durabilityQuery = "SELECT (DATEDIFF(COALESCE(dispose_date, NOW()), created_at) + inspector_points) - (YEAR(COALESCE(dispose_date, NOW())) - YEAR(created_at)) as durability FROM inventory WHERE status != 'disposed'";
$durabilityStmt = $conn->prepare($durabilityQuery);
$durabilityStmt->execute();
$durabilities = $durabilityStmt->fetchAll(PDO::FETCH_ASSOC);

$totalDurability = array_sum(array_column($durabilities, 'durability'));
$totalAssets = count($durabilities);
$averageDurability = $totalAssets > 0 ? $totalDurability / $totalAssets : 0;

// Prepare data for the horizontal bar chart
$needAttention = 0;
$good = 0;
$needInspection = 0;

foreach ($durabilities as $durability) {
    if ($durability['durability'] < 9 && $durability['durability'] >= 0) {
        $needAttention++;
    } elseif ($durability['durability'] >= 10 ) {
        $good++;
    } else {
        $needInspection++;
    }
}

$chartData = [
    ['x' => 'Good', 'y' => $good],
    ['x' => 'Need Attention', 'y' => $needAttention],
    ['x' => 'Need Inspection', 'y' => $needInspection],
];

// Ensure that variables are defined and sanitized
$totalAssets = isset($totalAssets) ? htmlspecialchars($totalAssets) : 0;
$deployedAssets = isset($deployedAssets) ? htmlspecialchars($deployedAssets) : 0;
$disposedAssets = isset($disposedAssets) ? htmlspecialchars($disposedAssets) : 0;
$pendingRequests = isset($pendingRequests) ? htmlspecialchars($pendingRequests) : 0;
$approvedRequests = isset($approvedRequests) ? htmlspecialchars($approvedRequests) : 0;

// Fetch categories for dropdown
$categoriesQuery = "SELECT category_id, name FROM categories";
$categoriesStmt = $conn->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subcategories for dropdown
$subcategoriesQuery = "SELECT subcategory_id, name FROM subcategory";
$subcategoriesStmt = $conn->prepare($subcategoriesQuery);
$subcategoriesStmt->execute();
$subcategories = $subcategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch brands for dropdown
$brandsQuery = "SELECT brand_id, brand_name FROM brands";
$brandsStmt = $conn->prepare($brandsQuery);
$brandsStmt->execute();
$brands = $brandsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>
    <?php if ($role == 'Faculty'): ?>
    <div class="flex-1 ml-64 p-4">
        <div class="assets-table mt-8 p-4 rounded-lg bg-white shadow-md">
            <h1 class="text-2xl font-bold mb-4">Deployed Assets</h1>

            <div class="flex justify-between items-center mb-4">
                <form action="../pdf/generateDeployed.php" method="post">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                        Download PDF
                    </button>
                </form>
            </div>

            <table id="deployedAssetsTable" class="display w-full table-auto border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">Asset ID</th>
                        <th class="px-4 py-2">Asset Name</th>
                        <th class="px-4 py-2">Brand</th>
                        <th class="px-4 py-2">Model</th>
                        <th class="px-4 py-2">QR Code</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deployedToUser as $asset): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($asset['id']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($asset['name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($asset['brandName']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($asset['modelName']); ?></td>
                            <td class="px-4 py-2">
                                <img src="<?php echo $asset['qrcode'] ? 'data:image/png;base64,' . base64_encode($asset['qrcode']) : 'default-qr.png'; ?>" 
                                     alt="QR Code" class="w-16 h-16 mx-auto">
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full <?php echo $asset['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo htmlspecialchars($asset['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

    <?php if ($role == 'Admin'): ?>
        <div class="flex-1 ml-64 p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-blue-500 text-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold">Total Assets</h2>
                    <p class="text-4xl mt-2 font-semibold"><?php echo $totalAssets; ?></p>
                </div>

                <div class="bg-green-500 text-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold">Deployed Assets</h2>
                    <p class="text-4xl mt-2 font-semibold"><?php echo $deployedAssets; ?></p>
                </div>

                <div class="bg-yellow-500 text-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold">Pending Requests</h2>
                    <p class="text-4xl mt-2 font-semibold"><?php echo $pendingRequests; ?></p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <div class="bg-gray-100 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold">Add Constants</h3>
                    <button class="mt-4 bg-blue-500 text-white py-2 px-4 rounded" data-toggle="modal" data-target="#addCategoryModal"> 
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                    <button class="mt-4 bg-blue-500 text-white py-2 px-4 rounded" data-toggle="modal" data-target="#addSubCategoryModal"> 
                        <i class="fas fa-plus"></i> Add Sub-Category
                    </button>
                    <button class="mt-4 bg-blue-500 text-white py-2 px-4 rounded" data-toggle="modal" data-target="#addBrandModal"> 
                        <i class="fas fa-plus"></i> Add Brand
                    </button>
                    <button class="mt-4 bg-blue-500 text-white py-2 px-4 rounded" data-toggle="modal" data-target="#addModelModal"> 
                        <i class="fas fa-plus"></i> Add Model
                    </button>
                </div>
                <div class="bg-gray-100 p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Extract all Deployed Assets from User</h3>
                    <form action="/PDF/extract_assets.php" method="post" class="flex items-center space-x-4">
                        <div class="form-group flex-grow">
                            <label for="userId" class="sr-only">Select User</label>
                            <select class="form-control py-2 px-2" id="userId" name="user_id">
                                <?php
                                    $usersQuery = "SELECT * FROM users";
                                    $usersStmt = $conn->prepare($usersQuery);
                                    $usersStmt->execute();
                                    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($users as $user) {
                                        echo '<option value="' . htmlspecialchars($user['id_number'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 text-white py-2 px-2 mb-2 rounded">
                            Extract Assets
                        </button>
                    </form>
                </div>
            </div>
            

            <!-- Add Category Modal -->
            <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="insert_values.php" method="post">
                                <input type="hidden" name="type" value="category">
                                <div class="form-group">
                                    <label for="categoryName">Category Name</label>
                                    <input type="text" class="form-control" id="categoryName" name="name" placeholder="Enter category name">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Sub-Category Modal -->
            <div class="modal fade" id="addSubCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addSubCategoryModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSubCategoryModalLabel">Add Sub-Category</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="insert_values.php" method="post">
                                <input type="hidden" name="type" value="subcategory">
                                <div class="form-group">
                                    <label for="subCategoryName">Sub-Category Name</label>
                                    <input type="text" class="form-control" id="subCategoryName" name="name" placeholder="Enter sub-category name">
                                </div>
                                <div class="form-group">
                                    <label for="categoryId">Category</label>
                                    <select class="form-control" id="categoryId" name="category_id">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Brand Modal -->
            <div class="modal fade" id="addBrandModal" tabindex="-1" role="dialog" aria-labelledby="addBrandModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addBrandModalLabel">Add Brand</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="insert_values.php" method="post">
                                <input type="hidden" name="type" value="brand">
                                <div class="form-group">
                                    <label for="brandName">Brand Name</label>
                                    <input type="text" class="form-control" id="brandName" name="name" placeholder="Enter brand name">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Model Modal -->
            <div class="modal fade" id="addModelModal" tabindex="-1" role="dialog" aria-labelledby="addModelModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModelModalLabel">Add Model</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="insert_values.php" method="post">
                                <input type="hidden" name="type" value="model">
                                <div class="form-group">
                                    <label for="modelName">Model Name</label>
                                    <input type="text" class="form-control" id="modelName" name="name" placeholder="Enter model name">
                                </div>
                                <div class="form-group">
                                    <label for="brandId">Brand</label>
                                    <select class="form-control" id="brandId" name="brand_id">
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo htmlspecialchars($brand['brand_id']); ?>">
                                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mt-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Assets Distribution</h2>
                    <div id="assetsDistributionChart"></div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-4">
                        <form action="../records/updateDurability.php" method="post">
                            <div class="flex items-center">
                                
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition duration-300">
                                  <i class = "fas fa-refresh"></i>  Update Durability
                                </button>
                            </div>
                        </form>
                    </div>
                    <h2 class="text-xl font-bold mb-4">Predictive Analysis</h2>
                    <div id="predictiveAnalysisChart"></div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                    // Assets Distribution Chart options
                    const assetsDistributionOptions = {
                        series: [
                            <?php echo $deployedAssets; ?>,
                            <?php echo $disposedAssets; ?>,
                            <?php echo $pendingRequests; ?>,
                            <?php echo $approvedRequests; ?>
                        ],
                        chart: {
                            type: 'pie',
                            height: 350,
                            animations: {
                                enabled: true
                            }
                        },
                        title: {
                            text: 'Assets Distribution',
                            align: 'center'
                        },
                        labels: ['Deployed','Disposed', 'Pending', 'In Stock'],
                        colors: ['#34D399','#EF4444', '#FBBF24', '#60A5FA'],
                        legend: {
                            position: 'bottom'
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return val.toFixed(1) + "%";
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return val + " assets";
                                }
                            }
                        }
                    };

                    // Add Predictive Analysis Chart options
                    const predictiveAnalysisOptions = {
                        series: [{
                            name: 'Asset Usage',
                            data: <?php echo json_encode(array_values($chartData)); ?>
                        }],
                        chart: {
                            type: 'bar',
                            height: 350,
                            animations: {
                                enabled: true
                            },
                            toolbar: {
                                show: true
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                dataLabels: {
                                    position: 'top'
                                },
                                borderRadius: 4,
                                distributed: true
                            }
                        },
                        xaxis: {
                            categories: ['Good', 'Needs Attention', 'Needs Inspection'],
                            labels: {
                                style: {
                                    colors: ['#22C55E', '#FBBF24', '#EF4444']
                                }
                            }
                        },
                        colors: ['#22C55E', '#FBBF24', '#EF4444'],
                        dataLabels: {
                            enabled: true,
                            offsetX: -6,
                            style: {
                                fontSize: '12px',
                                colors: ['#FFFFF']
                            },
                            background: {
                                enabled: true,
                                foreColor: '#fff',
                                borderRadius: 2,
                                padding: 4,
                                opacity: 0.9
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false
                        },
                        grid: {
                            show: true,
                            xaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        }
                    };

                    // Initialize charts with error handling
                    if (document.querySelector("#assetsDistributionChart")) {
                        const assetsDistributionChart = new ApexCharts(
                            document.querySelector("#assetsDistributionChart"), 
                            assetsDistributionOptions
                        );
                        assetsDistributionChart.render();
                    }

                    if (document.querySelector("#predictiveAnalysisChart")) {
                        const predictiveAnalysisChart = new ApexCharts(
                            document.querySelector("#predictiveAnalysisChart"), 
                            predictiveAnalysisOptions
                        );
                        predictiveAnalysisChart.render();
                    }
                
            });
        </script>
    <?php endif; ?>
</body>

</html>
