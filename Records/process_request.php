<?php
// process_request.php

session_start();
require 'includes/db.php'; // Include DB connection

if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    header('Location: view_request.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = $_POST['request_id'];
$action = $_POST['action'];

try {
    if ($action === 'approve') {
        // Check if the user has already approved this request
        $sql = "SELECT COUNT(*) FROM procurementapprove WHERE procurement_requestsID = :request_id AND userID = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); 
        $stmt->execute();
        $approvalExists = $stmt->fetchColumn();
        
        if ($approvalExists == 0) {
            // Get the current approve counter
            $sql = "SELECT approveCounter FROM procurement_requests WHERE id = :request_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($result) {
                $approve_count = $result['approveCounter'];
        
                if ($approve_count === null) {
                    $approve_count = 0; // Initialize if null
                }
        
                if ($approve_count < 4) {
                    $approve_count++;
                    $sql = "UPDATE procurement_requests SET approveCounter = :approve_count WHERE id = :request_id";
                } else {
                    $approve_count++;
                    $sql = "UPDATE procurement_requests SET approveCounter = :approve_count, status = 'Approved' WHERE id = :request_id";
                }
        
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':approve_count', $approve_count, PDO::PARAM_INT);
                $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
                $stmt->execute();
        
                // Insert the approval into approveTable
                $sql = "INSERT INTO procurementapprove (procurement_requestsID	, userID) VALUES (:request_id, :user_id)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
        
                // Log the activity
                $activity = "Approved Procurement Request";
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
    } elseif ($action === 'deny') {
        $sql = "UPDATE procurement_requests SET status = 'Denied' WHERE id = :request_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        throw new Exception('Invalid action');
    }

    // Log the deny action
    $activity = "Denied Procurement Request";
    $stmt = $conn->prepare("INSERT INTO userlogs (userID, activity) VALUES (:userID, :activity)");
    $stmt->bindParam(':userID', $user_id);
    $stmt->bindParam(':activity', $activity);
    $stmt->execute();

    header('Location: view_request.php');
    exit;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
