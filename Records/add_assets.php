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
<?php include __DIR__ . '/fetch_requests.php'; ?>

<div class="flex-1 ml-64 p-4">
    <div class="assets-table mt-8 p-4 rounded-lg bg-white">
        <h1 class="text-2xl font-bold mb-4">Inventory Dashboard</h1>
        
        <!-- Table displaying procurement requests -->
        <table id="requestsTable" class="display w-full">
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
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['procurement_request_id']); ?></td>
                    <td><?php echo htmlspecialchars($request['department_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['request_date']); ?></td>
                    <td><?php echo htmlspecialchars($request['last_name'].','.$request['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($request['unit_cost']); ?></td>
                    <td><?php echo htmlspecialchars($request['brand_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['model_name']); ?></td>
                    <td><?php echo htmlspecialchars($request['specs']); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td>
                        <div class="flex justify-center gap-2">
                            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Deploy</button>
                            <?php if ($role == 'Admin' || $role == 'Property Custodian'): ?>
                            <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Dispose</button>
                            <?php endif; ?>
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
</script>


