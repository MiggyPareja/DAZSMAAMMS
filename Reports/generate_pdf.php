<?php
// generate_pdf.php

session_start();
require 'includes/db.php'; // Include DB connection
require 'includes/TCPDF-main/tcpdf.php'; // Include TCPDF library

if (!isset($_POST['request_id'])) {
    header('Location: view_request.php');
    exit;
}

$request_id = $_POST['request_id'];

try {
    // Fetch request details along with the person in charge's name
    $sql = "SELECT pr.*, pic.name AS person_in_charge_name 
            FROM generate_request_requests pr
            LEFT JOIN persons_in_charge pic ON pr.requested_by = pic.id
            WHERE pr.requested_by = :request_id AND pr.status = 'Approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$requests) {
        echo "Request not found.";
        exit;
    }

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
    
    // Title
    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Cell(0, 10, 'REQUISITION FORM', 0, 1, 'C');
    $pdf->Ln(8);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Date of Request: ' . htmlspecialchars($requests[0]['date']), 0, 1);
    $pdf->Cell(0, 10, 'Requested By: ' . htmlspecialchars($requests[0]['person_in_charge_name']), 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 10);


    $pdf->SetFont('helvetica', '', 9);
    foreach ($requests as $request) {
  
    
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(170, 10, 'Asset Details', 1, 1);
        $pdf->Cell(170, 10, 'Reason for Disposal', 1, 1);
    
        $pdf->Cell(30, 7, 'Items', 1);
        $pdf->Cell(40, 7, 'Brand', 1);
        $pdf->Cell(40, 7, 'Specs', 1);
        $pdf->Cell(20, 7, 'Quantity', 1);
        $pdf->Cell(40, 7, 'Estimated unit_cost', 1);
        $pdf->Ln();
    
        $pdf->Cell(30, 7,  htmlspecialchars($request['particular_asset']), 1);
        $pdf->Cell(40, 7, htmlspecialchars($request['model']), 1);
        $pdf->Cell(40, 7, htmlspecialchars($request['specs']), 1);
        $pdf->Cell(20, 7, htmlspecialchars($request['quantity']), 1);
        $pdf->Cell(40, 7, htmlspecialchars($request['unit_cost']), 1);
        $pdf->Ln(10);
    }
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(30, 20, 'Endorsed by: 
    Mr. Osmond B. Baylen
    Principal ', 1, 'L', false, 0);
    $pdf->MultiCell(40, 20, 'Checked By: 
    Ms. Anna Liza M. Bernales
    Accounting Assistant ', 1, 'L', false, 0);
    $pdf->MultiCell(40, 20, 'Recommended By: 
    Rev. Fr. Gerardo I. Yabyabin, OSJ
    Treasurer ', 1, 'L', false, 0);
    $pdf->MultiCell(20, 20, 'Approved By: 
    Rev. Fr. Erwin B. Aguilar, OSJ
    Director', 1, 'L', false, 0);
    $pdf->MultiCell(40, 20, 'Released By: 
    Mrs. Lorna T. Villagracia
    Cashier ', 1, 'L', false, 1);

    // Output PDF
    $pdf->Output('Requisition_Form_' . $request_id . '.pdf', 'I');
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
