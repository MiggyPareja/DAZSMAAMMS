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


<?php include './fetch_requests.php'; ?>
<div class="flex">
    <!-- Sidebar -->
    <?php include '../sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 p-4 ml-64 overflow-x-auto">
        <div class="assets-table mt-8 p-4 rounded-lg bg-white shadow-md">
            <h1 class="text-2xl font-bold mb-4">Deployed Assets</h1>

            <!-- Flex container for the download button and search -->
            <div class="flex justify-between items-center mb-4 space-y-4"> 
                <!-- Download Button -->
                <form action="../pdf/generateDeployed.php" method="post">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                        Download PDF
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table id="deployedAssetsTable" class="w-full table-auto border-separate border-spacing-2 text-left">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border px-4 py-2">Asset ID</th>
                            <th class="border px-4 py-2">Asset Name</th>
                            <th class="border px-4 py-2">Brand</th>
                            <th class="border px-4 py-2">Model</th>
                            <th class="border px-4 py-2">Deployed to</th>
                            <th class="border px-4 py-2">Deployed at</th>
                            <th class="border px-4 py-2">QR Code</th>
                            <th class="border px-4 py-2">Status</th>
                            <th class="border px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deployed as $asset): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($asset['invID']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($asset['inventoryName']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($asset['brandName']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($asset['modelName']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($asset['lastName'] . ',' . $asset['firstName']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($asset['roomName']); ?></td>
                            <td class="border px-4 py-2">
                                <img src="<?php echo $asset['qrcode'] ? 'data:image/png;base64,' . base64_encode($asset['qrcode']) : 'default-qr.png'; ?>" alt="QR Code" class="w-16 h-16 mx-auto cursor-pointer qr-code">
                            </td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($asset['status']); ?></td>
                            <td class="border px-4 py-2">
                                <!-- Buttons -->
                                <button class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 inspect-btn" data-item-id="<?php echo htmlspecialchars($asset['invID']); ?>">Inspect</button>
                                <form action="./disposeAsset.php" method="post" class="inline-block">
                                    <input type="hidden" name="asset_id" value="<?php echo htmlspecialchars($asset['invID']); ?>">
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Dispose</button>
                                </form>
                                <!-- Transfer Button -->
                                <button class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transfer-btn" data-asset-id="<?php echo htmlspecialchars($asset['invID']); ?>">Transfer</button>
                                <!-- Recall Button -->
                                <button class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 recall-btn" data-asset-id="<?php echo htmlspecialchars($asset['invID']); ?>">Recall</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Inspect Modal -->
<div id="inspectionModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold mb-6">Inspection</h2>
        <form action="./inspectAssets.php" method="post">
            <div class="mb-6">
                <label class="block text-lg font-medium mb-2" hidden></label>
                <input type="text" name="item_id" class="w-full px-4 py-3 border rounded-md" readonly hidden>
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
            <div class="mb-6" hidden>
                <label class="block text-lg font-medium mb-2">Adjusted Points:</label>
                <input id="adjustedPoints" type="text" class="w-full px-4 py-3 border rounded-md bg-gray-100" value="0" readonly>
            </div>
            <!-- Comment -->
            <div class="mb-6">
                <label class="block text-lg font-medium mb-2">Comment:</label>
                <textarea name="comment" class="w-full px-4 py-3 border rounded-md" rows="4" placeholder="Enter your comments here..."></textarea>
            </div>
            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <button type="button" id="closeModal" class="bg-gray-500 text-white px-6 py-3 rounded-md hover:bg-gray-600">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Transfer Modal -->
<div id="transferModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg">
        <h2 class="text-2xl font-bold mb-6">Transfer Asset</h2>
        <form id="transferForm" action="./transferAsset.php" method="post">
            <input type="hidden" name="asset_id" id="transferAssetId">
            <!-- Add other form fields as needed -->
            <div class="mb-4">
                <label for="transferToUser" class="block text-sm font-medium text-gray-700">Transfer To:</label>
                <select id="transferToUser" name="transferToUser" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select a User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id_number']); ?>">
                            <?php echo htmlspecialchars($user['last_name'] . ', ' . $user['first_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Rooms Dropdown -->
            <div class="mb-4">
                <label for="transferToRoom" class="block text-sm font-medium text-gray-700">Transfer To Room:</label>
                <select id="transferToRoom" name="transferToRoom" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select a Room</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo htmlspecialchars($room['room_id']); ?>">
                            <?php echo htmlspecialchars($room['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2" onclick="closeTransferModal()">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- QR Code Modal -->
<div id="qrCodeModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-4">
        <img id="qrCodeModalImg" src="" alt="QR Code" class="w-full h-full">
        <button id="closeQrCodeModal" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
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
            const row = button.closest('tr');
            const itemName = row.querySelector('td:nth-child(2)').textContent; // Get the asset name from the second column
            modal.querySelector('input[name="item_id"]').value = itemId;  // Pass ID to the form
            modal.querySelector('input[name="item_name"]').value = itemName;  // Pass asset name to the form
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

document.querySelectorAll('.transfer-btn').forEach(button => {
    button.addEventListener('click', function() {
        const assetId = this.getAttribute('data-asset-id');
        document.getElementById('transferAssetId').value = assetId;
        document.getElementById('transferModal').classList.remove('hidden');
    });
});

function closeTransferModal() {
    document.getElementById('transferModal').classList.add('hidden');
}

document.querySelectorAll('.qr-code').forEach(img => {
    img.addEventListener('click', function() {
        const qrCodeSrc = this.src;
        const qrCodeModal = document.getElementById('qrCodeModal');
        const qrCodeModalImg = document.getElementById('qrCodeModalImg');
        qrCodeModalImg.src = qrCodeSrc;
        qrCodeModal.classList.remove('hidden');
    });
});

document.getElementById('closeQrCodeModal').addEventListener('click', function() {
    document.getElementById('qrCodeModal').classList.add('hidden');
});

window.addEventListener('click', function(e) {
    const qrCodeModal = document.getElementById('qrCodeModal');
    if (e.target === qrCodeModal) {
        qrCodeModal.classList.add('hidden');
    }
});

// Recall button functionality
document.querySelectorAll('.recall-btn').forEach(button => {
    button.addEventListener('click', function() {
        const assetId = this.getAttribute('data-asset-id');
        $.ajax({
            url: './recallAsset.php',
            type: 'POST',
            data: { asset_id: assetId },
            success: function(response) {
                alert('Asset status updated to Approved.');
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error updating asset status:', error);
                alert('Failed to update asset status.');
            }
        });
    });
});
</script>

</html>
