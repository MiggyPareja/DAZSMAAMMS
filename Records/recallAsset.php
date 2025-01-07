
<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = isset($_POST['asset_id']) ? trim($_POST['asset_id']) : '';

    if (!empty($asset_id)) {
        $updateQuery = "UPDATE inventory SET status = 'Approved' WHERE id = :asset_id";
        $updateStmt = $conn->prepare($updateQuery);

        if ($updateStmt->execute([':asset_id' => $asset_id])) {
            echo 'Success';
        } else {
            echo 'Failed to update asset status.';
        }
    } else {
        echo 'Invalid asset ID.';
    }
} else {
    echo 'Invalid request method.';
}
?>