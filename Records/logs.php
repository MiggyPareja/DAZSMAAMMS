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
    <!-- Include DataTables library -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<?php include '../sidebar.php'; ?>
<?php
// Include the database connection file
include '../includes/db.php';

// Fetch action logs from the database excluding disposed items
$stmt = $conn->prepare("SELECT * FROM logs ORDER BY log_date DESC");
$stmt->execute();
$action_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex-1 ml-64 p-4">
    <div class="assets-table mt-8 p-4 rounded-lg bg-white shadow-md">
        <h1 class="text-2xl font-bold mb-4">Logs</h1>

        <div class="flex space-x-4">
            <!-- First Div -->
            <div class="w-1/2 p-4 bg-gray-100 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-2">Action Logs</h2>
                <table id="actionLogsTable" class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2">Date</th>
                            <th class="py-2">Type</th>
                            <th class="py-2">Performed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($action_logs)): ?>
                            <?php foreach ($action_logs as $log): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($log['log_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($log['log_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($log['performed_by'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="border px-4 py-2 text-center">No logs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Second Div -->
            <div class="w-1/2 p-4 bg-gray-100 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-2">Transfer Logs</h2>
            </div>
        </div>
    </div>
</div>


<!-- Include jQuery and DataTables library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#actionLogsTable').DataTable({
            "order": [[0, "desc"]] // Order by the first column (Date) in descending order
        });
    });
</script>
</html>
