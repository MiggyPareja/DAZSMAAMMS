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

<body class="bg-cover bg-center h-screen bg-primary">

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col items-center justify-center ml-64">
        <h3 class="text-xl font-semibold mb-4 text-white text-left mt-4">Dashboard</h3>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-white shadow rounded-xl p-6 w-64 text-left">
                <div class="text-3xl font-bold">TODO</div>
                <div class="text-gray-500">Assets</div>
            </div>
            <div class="bg-white shadow rounded-xl p-6 w-64 text-left">
                <div class="text-3xl font-bold">TODO</div>
                <div class="text-gray-500">Assigned Assets</div>
                <button id="viewUnassignBtn" class="text-blue-500 mt-4 cursor-pointer">View Unassign</button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8">
            <div id="unassignContainer" class="bg-white shadow rounded-xl p-6 w-full mt-4 hidden">
                <h2 class="text-2xl font-bold mb-4">Unassigned Assets</h2>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Asset Name</th>
                            <th class="py-2 px-4 border-b">Total Count</th>
                        </tr>
                    </thead>
                    <tbody id="assetTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>