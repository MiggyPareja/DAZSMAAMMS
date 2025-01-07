<?php

require __DIR__ . '/fetch_requests.php';
require __DIR__ . '/../includes/db.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

    * {
        font-family: 'Poppins', sans-serif;
    }
</style>

<body class="bg-cover bg-center h-screen"
    style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('../images/Background.png');">
        
<?php include '../sidebar.php'; ?>

<div class="flex-1 ml-64 p-4">
    <div class="assets-table mt-8 p-4 rounded-lg bg-white">
        <h1 class="text-2xl font-bold mb-4">Request Dashboard</h1>
        
        <!-- Table displaying inventory requests -->
        <table id="requestsTable" class="display w-full">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Department</th>
                    <th>Request Date</th>
                    <th>Requested By</th>
                    <th>Unit Cost</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Specs</th>
                    <th>Category</th>
                    <th>Sub-Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr id="row-<?php echo htmlspecialchars($request['id']); ?>">
                    <td><?php echo htmlspecialchars($request['id']); ?></td>
                    <td><?php echo htmlspecialchars($request['department_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($request['last_name'].','.$request['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['unit_cost']); ?></td>
                    <td><?php echo htmlspecialchars($request['brand_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['model_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['specs']); ?></td>
                    <td><?php echo htmlspecialchars($request['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['subcategory_name']); ?></td>
                    <td id="status-<?php echo htmlspecialchars($request['id']); ?>">
                        <?php echo htmlspecialchars($request['status']); ?>
                    </td>
                    <td>
                        <div class="flex justify-center gap-2">
                            <button 
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600" 
                                onclick="updateStatus('<?php echo htmlspecialchars($request['id']); ?>', 'Approved')"
                            >
                                Approve
                            </button>
                            <button 
                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600" 
                                onclick="updateStatus('<?php echo htmlspecialchars($request['id']); ?>', 'Declined')"
                            >
                                Decline
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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

    function updateStatus(requestId, newStatus) {
        // Make an AJAX request to update the status in the backend
        fetch('update_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                requestId: requestId,
                newStatus: newStatus
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the status in the table
                document.getElementById(`status-${requestId}`).innerText = newStatus;

                // Log the action
                fetch('log_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        logType: newStatus === 'Approved' ? 'Approve Asset' : 'Decline Asset',
                        performedBy: <?php echo json_encode($_SESSION['id_number']); ?> // Assuming the current user ID is stored in the session
                    }),
                });
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>

</body>

</html>