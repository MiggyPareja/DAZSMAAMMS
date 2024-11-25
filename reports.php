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
    // Get person in charge ID and selected fields
    $person_id = intval($_POST['person_in_charge']);
    $selected_fields = isset($_POST['fields']) ? $_POST['fields'] : [];

    // Default fields if none selected
    if (empty($selected_fields)) {
        $selected_fields = ['Category', 'Sub-Category', 'Brand', 'Asset', 'Room Type', 'Room Comments'];
    }

    // Available fields in the database
    $available_fields = [
        'Category' => 'categories.name AS category',
        'Sub-Category' => 'sub_categories.name AS sub_category',
        'Brand' => 'assets.name AS brand',
        'Asset' => 'asset_records.unique_name AS asset',
        'Room Type' => 'room_types.name AS room_type',
        'Room Comments' => 'asset_records.comments AS comments',
    ];

    // Dynamically build the SELECT query based on selected fields
    $select_fields = [];
    foreach ($selected_fields as $field) {
        if (isset($available_fields[$field])) {
            $select_fields[] = $available_fields[$field];
        }
    }

    // If no fields are selected, use default columns
    if (empty($select_fields)) {
        $select_fields = array_values($available_fields);
    }

    // Join the selected fields into a string for the SELECT part of the query
    $select_query = implode(', ', $select_fields);

    // Dynamic SQL Query for "For Disposal" items
    $stmt = $conn->prepare("SELECT 
        $select_query,
        persons_in_charge.name AS person_in_charge
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
    $pdf->Ln(15);

    // Person in Charge Info
    $person_in_charge_name = html_entity_decode($forDisposalAssets[0]['person_in_charge'], ENT_QUOTES, 'UTF-8');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Person In Charge: ' . $person_in_charge_name, 0, 1);
    $pdf->Ln(5);

    // Table Header (dynamically generated based on selected fields)
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);

    foreach ($selected_fields as $field) {
        $pdf->Cell(30, 10, $field, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Table Content with Alternating Row Colors
    $pdf->SetFont('helvetica', '', 9);
    $rowColor = false;
    foreach ($forDisposalAssets as $asset) {
        $fillColor = $rowColor ? [245, 245, 245] : [255, 255, 255];
        $pdf->SetFillColorArray($fillColor);

        foreach ($selected_fields as $field) {
            // Map field name to the corresponding database column (e.g., 'Category' => 'category')
            $field_key = strtolower(str_replace(' ', '_', $field));
            $field_value = isset($asset[$field_key]) ? html_entity_decode($asset[$field_key], ENT_QUOTES, 'UTF-8') : '';
            $field_value = str_replace("&#039;", "'", $field_value); // Handle apostrophe issues
            $pdf->Cell(30, 8, htmlspecialchars($field_value), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $rowColor = !$rowColor; // Toggle row color
    }

    // Add space before signatures
    $pdf->Ln(15);

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

    // Output the PDF
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
    <!-- Existing Fields -->
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

    <!-- Dynamic Filters (Fields to Include in Report) -->
    <div>
        <label class="block text-sm font-medium text-gray-700">Fields to Include in Report:</label>
        <div class="flex flex-wrap gap-4 mt-2">
            <?php
            // Assuming $available_fields is an array fetched from a database or defined dynamically
            $available_fields = [
                'Category' => 'Category',
                'Sub-Category' => 'Sub-Category',
                'Brand' => 'Brand',
                'Asset' => 'Asset',
                'Room Type' => 'Room Type',
                'Room Comments' => 'Room Comments',
                // Add more fields dynamically here (can come from a DB query)
            ];

            foreach ($available_fields as $field => $label): ?>
                <div>
                    <input type="checkbox" id="<?php echo strtolower(str_replace(' ', '_', $field)); ?>" name="fields[]" value="<?php echo $field; ?>" class="mr-2">
                    <label for="<?php echo strtolower(str_replace(' ', '_', $field)); ?>" class="text-gray-700"><?php echo $label; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
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

