<?php
session_start();
require 'includes/db.php';
require_once 'includes/TCPDF-main/tcpdf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch session data
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query to count assets grouped by category and subcategory
        $query = "
           SELECT 
                c.name AS category,
                sc.name AS subcategory,
                COUNT(ar.id) AS asset_count
            FROM asset_records ar
            JOIN categories c ON ar.category_id = c.id
            JOIN sub_categories sc ON ar.sub_category_id = sc.id
            GROUP BY c.name, sc.name
            ORDER BY c.name, sc.name;
        ";

        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare report data
        $report = [];
        foreach ($results as $row) {
            $category = $row['category'];
            $subcategory = $row['subcategory'];
            $count = $row['asset_count'];

            if (!isset($report[$category])) {
                $report[$category] = [];
            }
            $report[$category][$subcategory] = $count;
        }

        // Create PDF
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Annual Asset Report');
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(TRUE, 20);
        $pdf->AddPage();

        // Header with Logos
        $pdf->Image('images/OSJLOGO.png', 15, 10, 30);
        $pdf->Image('images/DAZSMALOGO.png', 245, 10, 30);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Don Antonio De Zuzuarregui Sr. Memorial Academy Incorporated', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 5, 'St. Anthony, Brgy. Inarawan, Antipolo City', 0, 1, 'C');
        $pdf->Ln(15);

        // Report Content
        $pdf->SetFont('helvetica', '', 10);
        $html = "<h1>Annual Asset Report</h1>";
        foreach ($report as $category => $subcategories) {
            $category_total = array_sum($subcategories);
            $html .= "<h2>Category: $category - $category_total assets</h2>";
            $html .= "<ul>";
            foreach ($subcategories as $subcategory => $count) {
                $html .= "<li>$subcategory: $count</li>";
            }
            $html .= "</ul>";
        }

        $pdf->writeHTML($html, true, false, true, false, '');

        // Output the PDF
        $pdf->Output('Annual_Report.pdf', 'I');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header('Location: reports.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-cover bg-center h-screen"
    style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('images/Background.png');">
    <div class="flex-1 flex flex-col items-center justify-center">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-center">Annual Report</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 text-red-700 bg-red-100 rounded-lg">
                    <?php echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <form action="reports.php" method="POST" class="space-y-4">
                <div class="flex justify-center items-center mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
