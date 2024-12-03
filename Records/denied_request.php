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

 
            $sql = "UPDATE transfer_history SET  status = 'Denied' WHERE id = :request_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':approve_count', $approve_count, PDO::PARAM_INT);
     
   

        $activity = "Denied Request";
        $stmt = $conn->prepare("INSERT INTO userlogs (userID, activity) VALUES (:userID, :activity)");
        $stmt->bindParam(':userID', $user_id);
        $stmt->bindParam(':activity', $activity);
        $stmt->execute();
        header('Location: view_request.php?success=Transsfer Request Denied!');
        exit;

    
    header('Location: view_request.php?error=You already Approve this Requets!');
    exit;

    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>