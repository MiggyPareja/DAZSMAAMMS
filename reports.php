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

// Fetch session data
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get list of persons in charge
$persons_in_charge = fetchPersonsInCharge();

// Generate PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['person_in_charge'])) {
    $person_id = intval($_POST['person_in_charge']);

    // Query for "For Disposal" items
    $stmt = $conn->prepare("SELECT 
        categories.name AS category, 
        sub_categories.name AS sub_category, 
        assets.name AS brand,
        asset_records.unique_name AS asset,
        room_types.name AS room_type, 
        rooms.name AS room, 
        persons_in_charge.name AS person_in_charge,
        asset_records.comments AS comments
    FROM asset_records
    LEFT JOIN assets ON asset_records.asset_id = assets.id
    LEFT JOIN categories ON assets.category_id = categories.id
    LEFT JOIN sub_categories ON assets.sub_category_id = sub_categories.id
    LEFT JOIN rooms ON asset_records.room_id = rooms.id
    LEFT JOIN room_types ON rooms.room_type_id = room_types.id
    LEFT JOIN persons_in_charge ON asset_records.person_in_charge_id = persons_in_charge.id
    WHERE persons_in_charge.id = :person_id AND asset_records.disposed = 1");
    $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
    $stmt->execute();
    $forDisposalAssets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($forDisposalAssets)) {
        $_SESSION['error'] = "No disposal items found for the selected person in charge.";
        header('Location: reports.php');
        exit();
    }

    // Create PDF
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Disposed Assets Report');
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 20);
    $pdf->AddPage();

    // Header with Logos
    $logoLeft = 'images/OSJLOGO.png';
    $logoRight = 'images/DAZSMALOGO.png';

    $pdf->Image($logoLeft, 15, 10, 30);
    $pdf->Image($logoRight, 245, 10, 30);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Don Antonio De Zuzuarregui Sr. Memorial Academy Incorporated', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 5, 'St. Anthony, Brgy. Inarawan, Antipolo City', 0, 1, 'C');
    $pdf->Ln(15); // Add space

    // Person in Charge Info (decode HTML entities)
    $pdf->SetFont('helvetica', 'B', 12);
    $person_in_charge_name = html_entity_decode($forDisposalAssets[0]['person_in_charge'], ENT_QUOTES, 'UTF-8');
    $pdf->Cell(0, 10, 'Person In Charge: ' . $person_in_charge_name, 0, 1);
    $pdf->Ln(5);

    // Table Header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(30, 10, 'Category', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Sub-Category', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Brand', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Asset', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Room Type', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Room', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Comments', 1, 1, 'C', true);

    // Table Content with Alternating Row Colors
    $pdf->SetFont('helvetica', '', 9);
    $rowColor = false; // Alternating row color
    foreach ($forDisposalAssets as $asset) {
        $fillColor = $rowColor ? [245, 245, 245] : [255, 255, 255];
        $pdf->SetFillColorArray($fillColor);

        // Decode HTML entities for room and comments fields
        $room = html_entity_decode($asset['room'], ENT_QUOTES, 'UTF-8');
        $comments = html_entity_decode($asset['comments'], ENT_QUOTES, 'UTF-8');

        // Manually replace &#039; with ' (to handle apostrophe)
        $room = str_replace("&#039;", "'", $room);
        $comments = str_replace("&#039;", "'", $comments);

        // Debugging: Output the room and comments fields to verify
        error_log("Room: " . $room);
        error_log("Comments: " . $comments);

        $pdf->Cell(30, 8, htmlspecialchars($asset['category']), 1, 0, 'C', true);
        $pdf->Cell(30, 8, htmlspecialchars($asset['sub_category']), 1, 0, 'C', true);
        $pdf->Cell(30, 8, htmlspecialchars($asset['brand']), 1, 0, 'C', true);
        $pdf->Cell(30, 8, htmlspecialchars($asset['asset']), 1, 0, 'C', true);
        $pdf->Cell(35, 8, htmlspecialchars($asset['room_type']), 1, 0, 'C', true);
        $pdf->Cell(35, 8, htmlspecialchars($room), 1, 0, 'C', true);  // Decoded room
        $pdf->Cell(50, 8, htmlspecialchars($comments), 1, 1, 'C', true); // Decoded comments

        $rowColor = !$rowColor; // Toggle row color
    }

    $pdf->Ln(15); // Space before signatures

    // Signatures Section
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(255, 255, 255);
    $signatures = [
        ['Endorsed by', 'Mr. Osmond B. Baylen', 'Principal'],
        ['Checked by', 'Ms. Anna Liza M. Bernales', 'Accounting Assistant'],
        ['Recommended by', 'Rev. Fr. Gerardo I. Yabyabin, OSJ', 'Treasurer'],
        ['Approved by', 'Rev. Fr. Erwin B. Aguilar, OSJ', 'Director'],
        ['Released by', 'Mrs. Lorna T. Villagracia', 'Cashier'],
    ];

    foreach ($signatures as $signature) {
        $pdf->MultiCell(50, 25, "{$signature[0]}:\n\n" . htmlspecialchars($signature[1]) . "\n{$signature[2]}", 1, 'C', false, 0, '', '', true);
    }

    // Output PDF
    $pdf->Output('Disposed_Assets_Report.pdf', 'I');
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

