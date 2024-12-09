<?php
require '../includes/TCPDF-main/tcpdf.php'; // Correct path to TCPDF
require '../includes/db.php'; // Your database connection

// Create new PDF document (landscape orientation)
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // 'L' for landscape orientation

// Set document information
$pdf->SetCreator('DAZSMA');
$pdf->SetAuthor('DAZSMA');
$pdf->SetTitle('Deployed Assets Report');
$pdf->SetSubject('Deployed Assets');
$pdf->SetKeywords('TCPDF, PDF, assets, report');

// Add a page
$pdf->AddPage();

// Set title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'Deployed Assets Report', 0, 1, 'C');

// Fetch deployed assets from the database
$query = "SELECT 
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
        JOIN departments ON departments.department_id = inv.department_id
        JOIN users ON users.user_id = inv.requested_by
        JOIN categories c on c.category_id = inv.category_id
        JOIN subcategory s on s.subcategory_id = inv.subcategory_id
        JOIN rooms on rooms.room_id = inv.room_id
        WHERE inv.status = 'Deployed'";
$stmt = $conn->prepare($query);
$stmt->execute();
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Table Header
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, 'Asset Name', 1);
$pdf->Cell(30, 10, 'Brand', 1);
$pdf->Cell(30, 10, 'Model', 1);
$pdf->Cell(40, 10, 'Deployed To', 1);
$pdf->Cell(30, 10, 'Room', 1);
$pdf->Cell(40, 10, 'QR Code', 1); // Adding QR Code column
$pdf->Ln();

// Table Data
$pdf->SetFont('helvetica', '', 10);
foreach ($assets as $asset) {
    // Add Asset Data to the table
    $pdf->Cell(40, 10, $asset['inventoryName'], 1);
    $pdf->Cell(30, 10, $asset['brandName'], 1);
    $pdf->Cell(30, 10, $asset['modelName'], 1);
    $pdf->Cell(40, 10, $asset['lastName'] . ', ' . $asset['firstName'], 1);
    $pdf->Cell(30, 10, $asset['roomName'], 1);
    
    // Add QR Code to the table as an image (direct base64 decoding)
    if (!empty($asset['qrcode'])) {
        // Decode the base64 QR code string
        $qrcodeData = base64_decode($asset['qrcode']);
        
        // Save it to a temporary file
        $tempFilePath = 'temp_qrcode.png';
        file_put_contents($tempFilePath, $qrcodeData);

        // Add the QR code image to the PDF (width: 20mm, height: 20mm)
        $pdf->Image($tempFilePath, '', '', 20, 20, 'PNG');
        
        // Clean up the temporary QR code image
        unlink($tempFilePath);
    } else {
        $pdf->Cell(40, 10, 'No QR Code', 1);
    }
    
    $pdf->Ln();
}

// Save or Output the PDF
$pdf->Output('Deployed_Assets_Report.pdf', 'D'); // 'D' triggers the download
?>
