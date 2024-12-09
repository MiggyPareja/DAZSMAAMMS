<?php
require './includes/db.php';
require './sidebar.php';


$idNumber = $user ? htmlspecialchars($user['id_number']) : 'Unknown User';
$query = "SELECT brands.brand_name as brandName ,models.model_name as modelName,inv.*,rooms.name
    FROM inventory inv
    JOIN models on models.model_id = inv.model_id
    JOIN brands on brands.brand_id = inv.brand_id
    JOIN users on users.user_id = inv.requested_by
    JOIN rooms on rooms.room_id = inv.room_id
    WHERE inv.deployed_to = :idNumber";

$stmt = $conn->prepare($query); // Prepare the SQL statement
$stmt->bindValue(':idNumber', $idNumber, PDO::PARAM_INT); // Bind the id_number parameter
$stmt->execute(); // Execute the query

// Fetch the data
$deployedToUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<body>
    
    <?php if ($role == 'Faculty'): ?>
       <div class="flex-1 ml-64 p-4">
    <div class="assets-table mt-8 p-4 rounded-lg bg-white">
        <h1 class="text-2xl font-bold mb-4">Deployed Assets</h1>

        <!-- Flex container for the download button and search -->
        <div class="flex justify-between items-center mb-4">
            <!-- Download Button -->
            <form action="../pdf/generateDeployed.php" method="post">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Download PDF
                </button>
            </form>
        </div>

        <!-- Table displaying deployed assets -->
        <table id="deployedAssetsTable" class="display w-full table-auto border-separate border-spacing-2">
            <thead>
                <tr>
                    <th>Asset ID</th>
                    <th>Asset Name</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Deployed at</th>
                    <th>QR Code</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deployedToUser as $asset): ?>
                <tr>
                    <td><?php echo htmlspecialchars($asset['id']); ?></td>
                    <td><?php echo htmlspecialchars($asset['name']); ?></td>
                    <td><?php echo htmlspecialchars($asset['brandName']); ?></td>
                    <td><?php echo htmlspecialchars($asset['modelName']); ?></td>
                    <td><?php echo htmlspecialchars($asset['name']); ?></td>
                    <td>
                        <img src="<?php echo $asset['qrcode'] ? 'data:image/png;base64,' . base64_encode($asset['qrcode']) : 'default-qr.png'; ?>" alt="QR Code" class="w-16 h-16 mx-auto">
                    </td>
                    <td><?php echo htmlspecialchars($asset['status']); ?></td>
                </tr>
                <?php endforeach; ?>    
            </tbody>
        </table>
    </div>
</div>
    <?php endif; ?>
</body>

</html>
