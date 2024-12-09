<?php

session_start();
require 'includes/db.php'; // Include DB connection
require 'includes/TCPDF-main/tcpdf.php'; // Include TCPDF library


$request_id = $_POST['request_id'];


// Fetch the data from the database
$sql = "SELECT assets.id, 
               categories.name AS category, 
               sub_categories.name AS sub_category, 
               assets.name AS asset,
               room_types.name AS room_type, 
               rooms.name AS room, 
               persons_in_charge.name AS person_in_charge,
               asset_records.qrcode AS qrcode,
               asset_records.id AS asset_record_id,
               asset_records.specs,
               asset_records.disposal_date,
               asset_records.model
        FROM asset_records
        JOIN assets ON asset_records.asset_id = assets.id
        JOIN categories ON assets.category_id = categories.id
        JOIN sub_categories ON assets.sub_category_id = sub_categories.id
        LEFT JOIN rooms ON asset_records.room_id = rooms.id
        LEFT JOIN room_types ON rooms.room_type_id = room_types.id
        LEFT JOIN persons_in_charge ON asset_records.requested_by = persons_in_charge.id
        WHERE asset_records.id = '$request_id'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extend the TCPDF class to add a custom footer
class MYPDF extends TCPDF {
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'This is a system-generated document, Generated by:', 0, 1, 'C');
        $logo = 'images/SYSTEM LOGO.png'; 
        $this->Image($logo, $this->GetX() + 130, $this->GetY() - 10, 20);
    }
}

// Create new PDF document
$pdf = new MYPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$logoLeft = 'images/OSJLOGO.png'; // Replace with the actual path to the left logo
$logoRight = 'images/DAZSMALOGO.png'; // Replace with the actual path to the right logo

$pdf->Image($logoLeft, 10, 10, 30); // Adjust the x, y, and size as necessary
$pdf->Image($logoRight, 170, 10, 30); // Adjust the x, y, and size as necessary

$pdf->Cell(0, 15, 'Don Antonio De Zuzuarregui Sr. Memorial Academy Incorporated', 0, 1, 'C');
$pdf->Cell(0, 0, 'Brgy. Inarawan, Antipolo City', 0, 1, 'C');
$pdf->Ln(8);

$pdf->SetFont('helvetica', 'B', 15);
$pdf->Cell(0, 10, 'DISPOSAL FORM', 0, 1, 'C');
$pdf->Ln(8);


// Loop through assets and populate the PDF
foreach ($assets as $asset) {
    $pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Date of Request: '. $asset['disposal_date'], 0, 1);
$pdf->Cell(0, 10, 'Disposed By: ' . $asset['person_in_charge'], 0, 1); // Concatenation using a dot (.)
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 9);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(40, 10, 'Item', 1);
$pdf->Cell(50, 10, 'Brand', 1);
$pdf->Cell(30, 10, 'Specs', 1);
$pdf->Cell(50, 10, 'Current Room', 1);
$pdf->Ln();

    $pdf->Cell(40, 10, $asset['asset'], 1);
    $pdf->Cell(50, 10, $asset['model'], 1);
    $pdf->Cell(30, 10, $asset['specs'], 1);
    $pdf->Cell(50, 10, $asset['room'], 1);
    $pdf->Ln();
}

$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(170, 10, 'Disposal Method', 1, 1);

$pdf->Cell(56, 10, 'Sale', 1);
$pdf->Cell(56, 10, 'Scrap', 1);
$pdf->Cell(58, 10, 'Donation', 1);
$pdf->Ln(10);

$pdf->Cell(170, 10, 'Others Pls Specify:', 1, 1);
$pdf->Ln(10);

// Footer for endorsement
$pdf->SetFont('helvetica', 'B', 6);
$pdf->MultiCell(30, 20, 'Endorsed by: 
Mr. Osmond B. Baylen
Principal ', 1, 'L', false, 0);
$pdf->MultiCell(40, 20, 'Checked By: 
Ms. Anna Liza M. Bernales
Accounting Assistant ', 1, 'L', false, 0);
$pdf->MultiCell(30, 20, 'Recommended By: 
Rev. Fr. Gerardo I. Yabyabin, OSJ
Treasurer ', 1, 'L', false, 0);
$pdf->MultiCell(30, 20, 'Approved By: 
Rev. Fr. Erwin B. Aguilar, OSJ
Director', 1, 'L', false, 0);
$pdf->MultiCell(40, 20, 'Released By: 
Mrs. Lorna T. Villagracia
Cashier ', 1, 'L', false, 1);

// Output PDF
$pdf->Output('Disposal_Form.pdf', 'I');
exit;

?>
