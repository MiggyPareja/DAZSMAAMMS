<?php
include '../includes/TCPDF-main/tcpdf.php';
include '../includes/db.php';
include '../Records/fetch_requests.php';

// Create new PDF document with landscape orientation
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Approved Assets');
$pdf->SetSubject('Approved Assets Report');
$pdf->SetKeywords('TCPDF, PDF, approved, assets, report');

// Set default header data
$pdf->SetHeaderData('', 0, 'Approved Assets Report');

// Set header and footer fonts
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Column headings
$header = ['Request Id', 'Department', 'Request Date', 'Requested by', 'Quantity', 'Unit Cost', 'Brand', 'Model', 'Specs', 'Category', 'Subcategory'];

// Print table header
$html = '<h2>Approved Assets</h2>';
$html .= '<table border="1" cellpadding="4">';
$html .= '<tr>';
foreach ($header as $col) {
    $html .= "<th>{$col}</th>";
}
$html .= '</tr>';

// Print table rows
foreach ($inventory as $row) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['id']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['created_at']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['quantity']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['unit_cost']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['brand_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['model_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['specs']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['category_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['subcategory_name']) . '</td>';
    $html .= '</tr>';
}
$html .= '</table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('approved_assets.pdf', 'I');
?>