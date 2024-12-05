<?php
// check_request.php

session_start();
require 'includes/db.php'; // Include DB connection

// Check if the request_id is set
if (!isset($_POST['request_id'])) {
    header('Location: view_request.php');
    exit;
}

$request_id = $_POST['request_id'];

// Fetch request details
try {
    $sql = "SELECT * FROM generate_request_requests WHERE id = :request_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();

    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo "Request not found.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['unit_cost']) && isset($_POST['amount'])) {
    $unit_cost = $_POST['unit_cost'];
    $amount = $_POST['amount'];

    try {
        $sql = "UPDATE generate_request_requests SET unit_cost = :unit_cost, amount = :amount WHERE id = :request_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':unit_cost', $unit_cost);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->execute();

        header('Location: view_request.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Request</title>
    <style>
        form {
            margin: 20px 0;
        }
        label, input {
            display: block;
            margin: 10px 0;
        }
    </style>
    <script>
        // Function to confirm form submission
        function confirmSubmission(event) {
            if (!confirm("Are you sure you want to add these details?")) {
                event.preventDefault(); // Prevent form submission if not confirmed
            }
        }

        // Add event listener to form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', confirmSubmission);
        });
    </script>
</head>
<body>

<h1>Check Request</h1>

<form action="check_request.php" method="POST">
    <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">

    <label for="unit_cost">Unit Cost:</label>
    <input type="number" id="unit_cost" name="unit_cost" step="0.01" required>

    <label for="amount">Amount:</label>
    <input type="number" id="amount" name="amount" step="0.01" required>

    <button type="submit">Submit</button>
</form>

<a href="view_request.php">Go Back</a>

</body>
</html>
