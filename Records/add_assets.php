<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.16/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<?php include '../sidebar.php'; ?>
<?php include __DIR__ . '/fetch_requests.php'; ?>
<?php include __DIR__ . '/deploy_asset.php'; ?>

<div class="flex-1 ml-64 p-4">
    <div class="assets-table mt-8 p-4 rounded-lg bg-white">
        <h1 class="text-2xl font-bold mb-4">Inventory Dashboard</h1>

        <!-- Flex container for the download button and search -->
        <div class="flex justify-between items-center mb-4">
    <!-- Download Button -->
            <form action="../PDF/generate_pdf.php" method="post">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Download PDF
                </button>
            </form>
        </div>


        <!-- Table displaying procurement requests -->
        <table id="requestsTable" class="display w-full table-auto border-separate border-spacing-2">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Department</th>
                    <th>Request Date</th>
                    <th>Requested By</th>
                    <th>Quantity</th>
                    <th>Unit Cost</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Specs</th>
                    <th>Category</th>
                    <th>Sub-Category</th>
                    <th>Status</th>
                    <?php if ($role == 'Admin'): ?>
                    <th>Actions</th>
                     <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $request): ?>
                <tr id="row-<?php echo htmlspecialchars($request['id']); ?>">
                    <td><?php echo htmlspecialchars($request['id']); ?></td>
                    <td><?php echo htmlspecialchars($request['department_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($request['last_name'].','.$request['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($request['unit_cost']); ?></td>
                    <td><?php echo htmlspecialchars($request['brand_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['model_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['specs']); ?></td>
                    <td><?php echo htmlspecialchars($request['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['subcategory_name']); ?></td>
                    <td id="status-<?php echo htmlspecialchars($request['id']); ?>">
                        <?php echo htmlspecialchars($request['status']); ?>
                    </td>
                    
                    <?php if ($role == 'Admin'): ?>
                    <td>
                        
                        <div class="flex justify-center gap-2">
                           <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600" onclick="openModal(<?php echo $request['id']; ?>)">Deploy</button>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Deployment modal -->
<div id="deploymentModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg relative w-full max-w-lg">
        <!-- Close button -->
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700" onclick="closeModal()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <h2 class="text-2xl font-bold mb-6 text-center">Deploy Asset</h2>
        <form id="deployForm" method="POST" action="deploy_asset.php">
            <!-- Hidden Request ID -->
            <input type="hidden" id="requestId" name="requestId">

            <!-- Deploy to Room (Dropdown) -->
            <div class="mb-4">
                <label for="deployToRoom" class="block text-sm font-medium text-gray-700">Deploy to Room:</label>
                <select id="deployToRoom" name="deployToRoom" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select a Room</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo htmlspecialchars($room['room_id']); ?>">
                            <?php echo htmlspecialchars($room['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Deploy To (User Dropdown) -->
            <div class="mb-4">
                <label for="deployToUser" class="block text-sm font-medium text-gray-700">Deploy To:</label>
                <select id="deployToUser" name="deployToUser" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select a User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id_number']); ?>">
                            <?php echo htmlspecialchars($user['last_name'] . ', ' . $user['first_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Comments Section -->
            <div class="mb-4">
                <label for="comments" class="block text-sm font-medium text-gray-700">Comments:</label>
                <textarea id="comments" name="comments" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Add any comments here"></textarea>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="closeModal()">Cancel</button>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Deploy</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#requestsTable').DataTable({
            pageLength: 10, // default number of rows per page
            lengthMenu: [10, 30, 45, 100], // page length options
            searching: true, // enable search functionality
            ordering: true,  // enable sorting functionality
            responsive: true, // make the table responsive on smaller screens
        });
    });

    function openModal(requestId) {
    // Set the hidden input with the request ID
    document.getElementById('requestId').value = requestId;

    
    const request = <?php echo json_encode($inventory); ?>.find(r => r.id == requestId);
    document.getElementById('deployToRoom').value = request ? request.room_id : '';
    document.getElementById('deployToUser').value = request ? request.user_id : '';

    // Display the modal by removing the "hidden" class
    document.getElementById('deploymentModal').classList.remove('hidden');
}

function closeModal() {
    // Hide the modal by adding the "hidden" class
    document.getElementById('deploymentModal').classList.add('hidden');
}

    document.getElementById('downloadPdf').addEventListener('click', () => {
        // Send an AJAX request to generate the PDF on the server
        $.ajax({
            url: 'generateInventory.php', // PHP script that generates the PDF
            type: 'POST',
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.success) {
                    // If successful, you could handle success message here
                    window.location.href = response.pdfUrl; // Redirect to the generated PDF URL
                } else {
                    alert('Error generating PDF');
                }
            },
            error: function() {
                alert('An error occurred while generating the PDF.');
            }
        });
    });
</script>
