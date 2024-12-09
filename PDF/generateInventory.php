<?php
require_once '../includes/TCPDF-main/tcpdf.php'; // Include the TCPDF library

// Check if the TCPDF class is available
if (!class_exists('TCPDF')) {
    die('TCPDF class not found. Ensure TCPDF is installed correctly.');
}

// Create new PDF document with landscape orientation
try {
    // 'L' specifies landscape orientation
    $pdf = new TCPDF('L', 'mm', 'A4'); // 'L' for landscape, 'mm' for millimeters, 'A4' paper size

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('DAZSMA');
    $pdf->SetTitle('Inventory Dashboard');
    $pdf->SetSubject('Inventory Report');

    // Set default header and footer
    $pdf->setHeaderData('', 0, 'Inventory Dashboard', 'Generated by DAZSMA System');

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Add a page
    $pdf->AddPage();

    // Get inventory data (replace this with actual data fetching)
    $inventory = [
        
    ];

    // Create the table header
    $tableHtml = '<table border="1" cellpadding="4">';
    $tableHtml .= '<thead><tr><th>Request ID</th><th>Department</th><th>Request Date</th><th>Requested By</th><th>Quantity</th><th>Unit Cost</th><th>Brand</th><th>Model</th><th>Specs</th><th>Category</th><th>Sub-Category</th><th>Status</th></tr></thead>';
    $tableHtml .= '<tbody>';

    // Add table rows
    foreach ($inventory as $request) {
        $tableHtml .= '<tr>';
        $tableHtml .= '<td>' . $request['id'] . '</td>';
        $tableHtml .= '<td>' . $request['department_name'] . '</td>';
        $tableHtml .= '<td>' . $request['created_at'] . '</td>';
        $tableHtml .= '<td>' . $request['last_name'] . ', ' . $request['first_name'] . '</td>';
        $tableHtml .= '<td>' . $request['quantity'] . '</td>';
        $tableHtml .= '<td>' . $request['unit_cost'] . '</td>';
        $tableHtml .= '<td>' . $request['brand_name'] . '</td>';
        $tableHtml .= '<td>' . $request['model_name'] . '</td>';
        $tableHtml .= '<td>' . $request['specs'] . '</td>';
        $tableHtml .= '<td>' . $request['category_name'] . '</td>';
        $tableHtml .= '<td>' . $request['subcategory_name'] . '</td>';
        $tableHtml .= '<td>' . $request['status'] . '</td>';
        $tableHtml .= '</tr>';
    }

    $tableHtml .= '</tbody></table>';

    // Write the HTML table to the PDF
    $pdf->writeHTML($tableHtml, true, false, false, false, '');

    // Output the PDF to the browser
    $pdfOutputName = 'inventory_dashboard_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($pdfOutputName, 'I'); // 'I' for inline view in browser

} catch (Exception $e) {
    echo 'Error generating PDF: ' . $e->getMessage();
}
?>