<?php
ob_start(); // Start output buffering

include '../includes/db.php';
require '../includes/TCPDF-main/tcpdf.php'; // Include TCPDF library

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];

        // Query to get all deployed items for the selected user
        $query = "SELECT 
                inv.id as invID,
                inv.name as inventoryName,
                brands.brand_name as brandName,
                models.model_name as modelName,
                rooms.name as roomName,
                users.first_name as firstName,
                users.last_name as lastName,
                inv.qrcode,
                inv.status
            FROM inventory inv
            JOIN brands ON brands.brand_id = inv.brand_id
            JOIN models ON models.model_id = inv.model_id
            JOIN users ON users.id_number = inv.deployed_to
            JOIN rooms on rooms.room_id = inv.room_id
            WHERE users.id_number = :id_number";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_number', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$items) {
            echo "No items found for the selected user.";
            exit;
        }

        // Extend the TCPDF class to add a custom footer
        class MYPDF extends TCPDF {
            public function Footer() {
                $this->SetY(-15);
                $this->SetFont('helvetica', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }

        // Create new PDF document in landscape mode
        $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Deployed Assets Report');
        $pdf->SetSubject('Deployed Assets');
        $pdf->SetKeywords('TCPDF, PDF, assets, report');

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Add content
        $html = '<h1>Deployed Assets to User Report</h1>';
        $html .= '<table border="1" cellpadding="4">';
        $html .= '<thead><tr><th>ID</th><th>Name</th><th>Brand</th><th>Model</th><th>Room</th><th>User</th><th>Status</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['invID'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['inventoryName'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['brandName'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['modelName'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['roomName'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['firstName'] . ' ' . $item['lastName'], ENT_QUOTES, 'UTF-8') . '</td>';
            
            $html .= '<td>' . htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        $pdf->Output('deployed_assets_report.pdf', 'I');
    } else {
        echo "No user selected.";
    }
} else {
    echo "Invalid request method.";
}

ob_end_flush(); // End output buffering and flush output
?>