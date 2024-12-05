<?php
// process_request.php

session_start();
require 'includes/db.php'; // Include DB connection

if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    header('Location: view_request.php');
    exit;
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$request_id = $_POST['request_id'];
$action = $_POST['action'];

try {
    // Check if the user has already approved this request
    $sql = "SELECT COUNT(*) FROM approveTable WHERE request_Id = :request_id AND user_Id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $approvalExists = $stmt->fetchColumn();

    if ($approvalExists == 0) {
        // Retrieve current approval count
        $sql = "SELECT approve_count, particular_asset, quantity FROM transfer_history JOIN generate_request_requests ON transfer_history.id = generate_request_requests.id WHERE transfer_history.id = :request_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $approve_count = $result['approve_count'] ?? 0;
            $particular_asset = $result['particular_asset'];
            $quantity = $result['quantity'];

            if ($approve_count < 4) {
                $approve_count++;
                $sql = "UPDATE transfer_history SET approve_count = :approve_count WHERE id = :request_id";
                $stmt = $conn->prepare($sql);
            } else {
                $approve_count++;
                $sql = "UPDATE transfer_history SET approve_count = :approve_count, status = 'Approved' WHERE id = :request_id";
                $stmt = $conn->prepare($sql);

                // Insert the asset into the assets table after approval
                $asset_sql = "INSERT INTO assets (name, assetCount) VALUES (:name, :quantity)";
                $asset_stmt = $conn->prepare($asset_sql);
                $asset_stmt->bindParam(':name', $particular_asset, PDO::PARAM_STR);
                $asset_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $asset_stmt->execute();
            }

            $stmt->bindParam(':approve_count', $approve_count, PDO::PARAM_INT);
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->execute();

            // Insert the approval into approveTable
            $sql = "INSERT INTO approveTable (request_Id, user_Id) VALUES (:request_id, :user_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Log the user's action
            $activity = "Approved Request";
            $stmt = $conn->prepare("INSERT INTO userlogs (userID, activity) VALUES (:userID, :activity)");
            $stmt->bindParam(':userID', $user_id);
            $stmt->bindParam(':activity', $activity);
            $stmt->execute();

            header('Location: view_request.php');
            exit;
        }
    } else {
        header('Location: view_request.php?error=You already approved this request!');
        exit;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
