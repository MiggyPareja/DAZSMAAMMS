<?php
require 'vendor/autoload.php'; // QR code library, e.g., PHP QR Code

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (isset($_GET['data'])) {
    $data = $_GET['data']; // e.g., '12345'
    $qrCode = new QrCode($data);
    $writer = new PngWriter();
    header('Content-Type: image/png');
    echo $writer->writeString($qrCode)->getString();
} else {
    echo 'No data provided';
}
?>

