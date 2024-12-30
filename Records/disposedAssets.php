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

<?php include '../sidebar.php'; ?>
<?php include './deploy_asset.php'; ?>
<?php include './fetch_requests.php'; ?>

<div class="flex-1 ml-64 p-4">
    <div class="assets-table mt-8 p-4 rounded-lg bg-white shadow-md">
        <h1 class="text-2xl font-bold mb-4">Disposed Assets</h1>

        <!-- Flex container for the download button and search -->
        <div class="flex justify-between items-center mb-4">
            <!-- Download Button -->
            <form action="../pdf/generateDisposed.php" method="post">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Download PDF
                </button>
            </form>
        </div>

        <!-- Table displaying deployed assets -->
        <table id="disposedAssetsTable" class="display w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 font-semibold text-gray-700">Asset Name</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Brand</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Model</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Deployed to</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Deployed at</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">QR Code</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Disposed Date</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Action</th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disposed as $asset): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-3"><?php echo htmlspecialchars($asset['inventoryName']); ?></td>
                    <td class="px-6 py-3"><?php echo htmlspecialchars($asset['brandName']); ?></td>
                    <td class="px-6 py-3"><?php echo htmlspecialchars($asset['modelName']); ?></td>
                    <td class="px-6 py-3"><?php echo htmlspecialchars($asset['lastName'] . ',' . $asset['firstName']); ?></td>
                    <td class="px-6 py-3"><?php echo htmlspecialchars($asset['roomName']); ?></td>
                    <td class="px-6 py-3 text-center">
                        <img src="<?php 
                            echo $asset['qrcode'] 
                                ? 'data:image/png;base64,' . base64_encode($asset['qrcode']) 
                                : 'default-qr.png'; ?>" 
                            alt="QR Code" 
                            class="w-16 h-16 mx-auto"
                        >
                    </td>
                    <td class="px-6 py-3"><?php echo htmlspecialchars($asset['disposed_date']); ?></td>
                    <td class="px-6 py-3"><?php echo htmlspecialchars($asset['status']); ?></td>
                    <td class="px-6 py-3 text-center">
                        <form action="redeploy_asset.php" method="post">
                            <input type="hidden" name="asset_id" value="<?php echo $asset['invId']; ?>">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Redeploy</button>
                        </form>
                    </td> <!-- New action button -->
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#disposedAssetsTable').DataTable({
            "pageLength": 10, // Set the default number of rows per page
            "lengthMenu": [10, 25, 50, 100], // Allows the user to select the number of rows to show
            "language": {
                "lengthMenu": "Show _MENU_ entries", // Customizes the text for the length menu
            }
        });
    });
</script>
