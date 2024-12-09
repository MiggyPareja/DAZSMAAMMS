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
                    <th>Deployed to</th>
                    <th>Deployed at</th>
                    <th>QR Code</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deployed as $asset): ?>
                <tr>
                    <td><?php echo htmlspecialchars($asset['invID']); ?></td>
                    <td><?php echo htmlspecialchars($asset['inventoryName']); ?></td>
                    <td><?php echo htmlspecialchars($asset['brandName']); ?></td>
                    <td><?php echo htmlspecialchars($asset['modelName']); ?></td>
                    <td><?php echo htmlspecialchars($asset['lastName'] . ',' . $asset['firstName']); ?></td>
                    <td><?php echo htmlspecialchars($asset['roomName']); ?></td>
                    <td>
                        <img src="<?php echo $asset['qrcode'] ? 'data:image/png;base64,' . base64_encode($asset['qrcode']) : 'default-qr.png'; ?>" alt="QR Code" class="w-16 h-16 mx-auto">
                    </td>
                    <td><?php echo htmlspecialchars($asset['status']); ?></td>
                    <td>
                        <!-- Pass asset ID instead of name -->
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 inspect-btn" data-item-id="<?php echo htmlspecialchars($asset['invID']); ?>">
                            Inspect
                        </button>
                        <form action="./disposeAsset.php" method="post">
                            <input type="hidden" name="asset_id" value="<?php echo htmlspecialchars($asset['invID']); ?>">
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Dispose</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>    
            </tbody>
        </table>
    </div>
</div>

<!-- Modal HTML -->
<div id="inspectionModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold mb-6">Inspection</h2>
        <form action="./inspectAssets.php" method="post">
            <div class="mb-6">
                <label class="block text-lg font-medium mb-2" hidden>Item:</label>
                <input type="text" name="item_id" class="w-full px-4 py-3 border rounded-md" value="TEST" readonly hidden>
            </div>
            <!-- Item Name -->
            <div class="mb-6">
                <label class="block text-lg font-medium mb-2">Item:</label>
                <input type="text" name="item_name" class="w-full px-4 py-3 border rounded-md" value="TEST" readonly>
            </div>
            <!-- Maintenance Type -->
            <div class="mb-6">
                <label class="block text-lg font-medium mb-2">Maintenance Type:</label>
                <select id="maintenanceType" name="maintenance_type" class="w-full px-4 py-3 border rounded-md">
                    <option value="0">Select maintenance type...</option>
                    <option value="0">No Repair</option>
                    <option value="-5">Minor Repair</option>
                    <option value="-10">Major Repair</option>
                    <option value="-15">For Disposal</option>
                </select>
            </div>
            <!-- Adjusted Points -->
            <div class="mb-6">
                <label class="block text-lg font-medium mb-2">Adjusted Points:</label>
                <input id="adjustedPoints" type="text" class="w-full px-4 py-3 border rounded-md bg-gray-100" value="0" readonly>
            </div>
            <!-- Inspection History -->
            <div class="mb-6">
                <label class="block text-lg font-medium mb-2">Inspection History:</label>
                <textarea class="w-full px-4 py-3 border rounded-md bg-blue-100" rows="6" readonly>See more, refresh.</textarea>
            </div>
            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <button type="button" id="closeModal" class="bg-gray-500 text-white px-6 py-3 rounded-md hover:bg-gray-600">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600">Submit</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<script>
    $(document).ready(function() {
        $('#deployedAssetsTable').DataTable({
            "pageLength": 10, // Set the default number of rows per page
            "lengthMenu": [10, 25, 50, 100], // Allows the user to select the number of rows to show
            "language": {
                "lengthMenu": "Show _MENU_ entries", // Customizes the text for the length menu
            }
        });
    });

 document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('inspectionModal');
    const closeModal = document.getElementById('closeModal');
    const inspectButtons = document.querySelectorAll('.inspect-btn');
    const maintenanceType = document.getElementById('maintenanceType');
    const adjustedPoints = document.getElementById('adjustedPoints');

    // Show modal on inspect button click
    inspectButtons.forEach(button => {
        button.addEventListener('click', () => {
            const itemId = button.getAttribute('data-item-id');
            modal.querySelector('input[name="item_id"]').value = itemId;  // Pass ID to the form
            modal.classList.remove('hidden');
        });
    });

    // Update points when maintenance type changes
    maintenanceType.addEventListener('change', () => {
        // Get the selected value
        const selectedType = maintenanceType.value;

        // Update the adjusted points based on the maintenance type
        switch (selectedType) {
            case "0":
                adjustedPoints.value = "0"; // No Repair
                break;
            case "-5":
                adjustedPoints.value = "-5"; // Minor Repair
                break;
            case "-10":
                adjustedPoints.value = "-10"; // Major Repair
                break;
            case "-15":
                adjustedPoints.value = "-15"; // For Disposal
                break;
            default:
                adjustedPoints.value = "0"; // Default to No Repair if no valid option
        }
    });

    // Close modal on cancel button click
    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});

</script>

</html>
