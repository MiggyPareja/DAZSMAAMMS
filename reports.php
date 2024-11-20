<?php
session_start();
require 'includes/db.php';
require_once 'includes/TCPDF-main/tcpdf.php'; // Assuming you have the TCPDF library installed

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch options for Person In Charge dropdown
function fetchPersonsInCharge()
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, name FROM persons_in_charge");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch username from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user ? htmlspecialchars($user['username']) : 'Unknown User';

$persons_in_charge = fetchPersonsInCharge();

// Generate PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['person_in_charge'])) {
    $person_id = intval($_POST['person_in_charge']);

    // Query for "For Disposal" items
    $stmt = $conn->prepare("SELECT categories.name AS category, 
                                   sub_categories.name AS sub_category, 
                                   assets.name AS asset,
                                   room_types.name AS room_type, 
                                   rooms.name AS room, 
                                   persons_in_charge.name AS person_in_charge,
                                   asset_records.comments AS comments
                            FROM asset_records
                            JOIN assets ON asset_records.asset_id = assets.id
                            JOIN categories ON assets.category_id = categories.id
                            JOIN sub_categories ON assets.sub_category_id = sub_categories.id
                            LEFT JOIN rooms ON asset_records.room_id = rooms.id
                            LEFT JOIN room_types ON rooms.room_type_id = room_types.id
                            LEFT JOIN persons_in_charge ON asset_records.person_in_charge_id = persons_in_charge.id
                            WHERE persons_in_charge.id = ? AND asset_records.disposed = 1");
    $stmt->execute([$person_id]);
    $forDisposalAssets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($forDisposalAssets)) {
        // No disposal items found
        $_SESSION['error'] = "No disposal items found for the selected person in charge.";
        header('Location: reports.php');
        exit();
    }

    // Create PDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $logoLeft = 'images/OSJLOGO.png'; // Replace with the actual path to the left logo
    $logoRight = 'images/DAZSMALOGO.png'; // Replace with the actual path to the right logo

    $pdf->Image($logoLeft, 10, 10, 30); // Adjust the x, y, and size as necessary
    $pdf->Image($logoRight, 170, 10, 30); // Adjust the x, y, and size as necessary
    $pdf->Cell(0, 15, 'Don Antonio De Zuzuarregui Sr. Memorial Academy Incorporated', 0, 1, 'C');
    $pdf->Cell(0, 0, 'St. Anthony, Brgy. Inarawan, Antipolo City', 0, 1, 'C');
    $pdf->Ln(20); // Add some space after the title

    // Person In Charge Info
    $pdf->Cell(0, 10, 'Person In Charge: ' . htmlspecialchars($forDisposalAssets[0]['person_in_charge']), 0, 1);
    $pdf->Ln(5);

    // Space between sections
    $pdf->Ln(10);

    // "For Disposal" section
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(190, 7, 'Disposed Assets', 1, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(30, 7, 'Category', 1);
    $pdf->Cell(30, 7, 'Sub-Category', 1);
    $pdf->Cell(30, 7, 'Asset', 1);
    $pdf->Cell(30, 7, 'Room Type', 1);
    $pdf->Cell(35, 7, 'Room', 1);
    $pdf->Cell(35, 7, 'Comments', 1);
    $pdf->Ln();

    $pdf->SetFont('helvetica', '', 8);
    foreach ($forDisposalAssets as $asset) {
        $pdf->Cell(30, 12, $asset['category'], 1);
        $pdf->Cell(30, 12, $asset['sub_category'], 1);
        $pdf->Cell(30, 12, $asset['asset'], 1);
        $pdf->Cell(30, 12, $asset['room_type'], 1);
        $pdf->Cell(35, 12, $asset['room'], 1);
        $pdf->Cell(35, 12, $asset['comments'], 1);
        $pdf->Ln();
    }

    //Signatures
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(37, 20, 'Endorsed by: 
     
    


    Mr. Osmond B. Baylen
               Principal ', 1, 'L', false, 0, '', '', true, 0, false, true, 20, 'T', true);
    $pdf->MultiCell(37, 20, 'Checked By: 
     
    
 

    Ms. Anna Liza M. Bernales
         Accounting Assistant ', 1, 'L', false, 0, '', '', true, 0, false, true, 20, 'T', true);
    $pdf->MultiCell(42, 20, 'Recommended By: 
     
    


    Rev.Fr. Gerardo I. Yabyabin, OSJ
                           Treasurer ', 1, 'L', false, 0, '', '', true, 0, false, true, 20, 'T', true);
    $pdf->MultiCell(37, 20, 'Approved By: 
     
    


     Rev.Fr. Erwin B. Aguilar, OSJ
                          Director', 1, 'L', false, 0, '', '', true, 0, false, true, 20, 'T', true);
    $pdf->MultiCell(37, 20, 'Released By: 
     
    


    Mrs. Lorna T. Villagracia
                      Cashier ', 1, 'L', false, 1, '', '', true, 0, false, true, 20, 'T', true);

    // Output PDF
    $pdf->Output('report.pdf', 'I');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
          @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

* {
    font-family: 'Poppins', sans-serif;
}
</style>
</head>


<body class="bg-cover bg-center h-screen"
    style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('images/Background.png');">
        <a>
        <div class="bg-blue-900 w-64 flex flex-col p-4 fixed h-full space-y-4 z-20">
              <div class="image">
                  <img src="images/SYSTEM LOGO 2.png" alt="User Image" class="text-white text-left">
              </div>
        </a>



        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
              <div class="image">
                  <img src="images/avatar.png" class="rounded-full w-12 h-12" alt="User Image">
              </div>
              <div class="info">
                  <a href="#" class="d-block text-white"><?php echo $username; ?></a>
              </div>
          </div>
            <nav class="flex flex-col space-y-4">
                <?php if ($role == 'Admin' || $role == 'Property Custodian' || $role == 'Inspector'): ?>
                    <!-- Dashboard -->
                    <a href="dashboard.php">
                        <button class="nav-icon fas fa-tachometer-alt text-white text-sm"> Dashboard</button>             
                    </a>

                    <!-- Collapsible Records Section -->
                    <a>
                    <button id="recordsBtn" class="nav-icon fas fa-folder text-white text-sm"> Records
    <span id="arrow" class="transform transition-transform">&#9660;</span>
</button></a>
                    <div id="recordsMenu" class="hidden flex flex-col p-2 space-y-3">
                        <a href="view_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Assets</button>
                        </a>
                        <?php if ($role == 'Admin' || $role == 'Property Custodian'): ?>
                        <a href="dispose_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Disposed Assets</button>
                        </a>
                        <a href="add_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Deploy Assets</button>
                        </a>
                        <?php endif; ?>
                        <a href="view_request.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Requests</button>
                        </a>
                        <a href="procurement.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Generate Request</button>
                        </a>
                    </div>
                    
                    <!-- Reports -->
                    <a>
                    <button id="reportsBtn" class="nav-icon fas fa-chart-bar text-white text-sm"> Reports
    <span id="arrow" class="transform transition-transform">&#9660;</span>
</button></a>
                    <div id="reportsMenu" class="hidden flex flex-col p-2 space-y-3">
                        <a href="reports.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Person-In-Charge</button>
                        </a>
                        <a href="asset_durability.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Asset Durability</button>
                        </a>
                    </div>

                    <!-- User Management for Admin Only -->
                    <?php if ($role == 'Admin'): ?>
                        <a href="manage_users.php">
                            <button class="nav-icon fas fa-id-card text-white text-sm"> User Management</button>
                        </a>
                    <?php endif; ?>

                    <!-- Log Out -->
                    <a href="logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>

                <?php elseif ($role == 'Faculty'): ?>
                    <!-- Faculty Specific Options -->
                    <a href="reports.php">
                        <button class="nav-icon fas fa-folder text-white text-sm"> Reports</button>
                    </a>
                    <a href="procurement.php">
                        <button class="far fa-circle nav-icon text-white text-sm"> Generate Request</button>
                    </a>
                    <a href="logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>
                <?php endif; ?>
            </nav>
        </div>

      
<script>
        document.getElementById('recordsBtn').addEventListener('click', function () {
            const recordsMenu = document.getElementById('recordsMenu');
            const arrow = document.getElementById('arrow');
            recordsMenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });

        document.getElementById('reportsBtn').addEventListener('click', function () {
            const reportsMenu = document.getElementById('reportsMenu');
            const arrow = document.getElementById('arrow');
            reportsMenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });
</script>
      

    
        <div class="flex-1 flex flex-col items-center justify-center ml-64">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md mt-40">
        <h2 class="text-2xl font-bold mb-6 text-center">Person-In-Charge Reports</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 text-red-700 bg-red-100 rounded-lg">
                <?php echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="reports.php" method="POST" class="space-y-4">
            <div>
                <label for="person_in_charge" class="block text-sm font-medium text-gray-700">Select Person In Charge:</label>
                <select id="person_in_charge" name="person_in_charge" required class="mt-1 block w-full p-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Person In Charge</option>
                    <?php foreach ($persons_in_charge as $person): ?>
                        <option value="<?php echo htmlspecialchars($person['id']); ?>">
                            <?php echo htmlspecialchars($person['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-center items-center mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Generate Report
                </button>
            </div>
        </form>
    </div>
 
        </div>

    </div>

</body>


</html>

