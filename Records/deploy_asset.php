<?php

require '../includes/db.php';
require_once '../includes/phpqrcode/phpqrcode.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form data
    $requestId = $_POST['requestId'];
    $roomId = $_POST['deployToRoom'];
    $deployedToUser = $_POST['deployToUser']; // The user to which the asset is deployed
    $comments = $_POST['comments'];

    // Debugging: Check if the deployed_to value is coming through
    var_dump($deployedToUser); // Output deployed_to for debugging

    // Retrieve asset from the database using the requestId
    $query = "SELECT * FROM inventory
    JOIN models on models.model_id = inventory.model_id
    JOIN brands on brands.brand_id = inventory.brand_id
    JOIN users on users.user_id = inventory.requested_by
    WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$requestId]);
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$asset) {
        die('Asset not found.');
    }
    $brandName = $asset['brand_name']; 
    $modelName = $asset['model_name']; 
    $deployedTo = $asset['deployed_to']; // This is the previously stored value
    $lastInspected = $asset['last_inspected'];

    // Get current date and time
    $currentDateTime = date('Y-m-d H:i:s'); // Format: Year-Month-Day Hour:Minute:Second

    // Generate unique name in the required format: brand_name + model_name + date/time
    $uniqueName = $brandName . '- ' . $modelName . '- ' . $currentDateTime;

    // Update the inventory table first with the data
    $updateQuery = "UPDATE inventory 
                    SET name = ?, 
                        room_id = ?, 
                        deployed_to = ?, 
                        comments = ?, 
                        status = 'Deployed'  
                    WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute([
        $uniqueName,
        $roomId,
        $deployedToUser, // Ensure this is set correctly
        $comments,
        $requestId
    ]);

    // Debugging: Check if the update query was successful
    if ($stmt->rowCount() === 0) {
        die("Error updating asset.");
    }

    // Fetch the updated data from the database
    $infoQuery = "SELECT subcategory.name as subcategoryName, categories.name as categoryName, rooms.name as roomName, rooms.room_type as roomType, inventory.* 
    FROM inventory
    LEFT JOIN models ON models.model_id = inventory.model_id
    LEFT JOIN brands ON brands.brand_id = inventory.brand_id
    LEFT JOIN rooms ON rooms.room_id = inventory.room_id
    LEFT JOIN subcategory ON subcategory.subcategory_id = inventory.subcategory_id
    LEFT JOIN categories ON categories.category_id = inventory.category_id
    WHERE inventory.id = ?";  // Add the condition to filter by requestId

    $stmt = $conn->prepare($infoQuery);
    $stmt->execute([$requestId]);  // Pass requestId to execute
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        var_dump($requestId); 
        var_dump($info); 
        die('QR not found.');
    }

    // Assign the necessary values for QR code generation
    $categoryName = $info['categoryName'];
    $subcategoryName = $info['subcategoryName'];
    $roomName = $info['roomName']; 
    $roomType = $info['roomType']; 

    // Debugging: Check if the deployed_to value is correctly updated
    $deployedTo = $info['deployed_to']; // Fetch the latest deployed_to value from the updated record
    var_dump($deployedTo); // Debugging: Output deployed_to to confirm

    // Generate the QR code data
    $qrcodeData = "Category: " . $categoryName . "\n" . 
                  "Sub-Category: " . $subcategoryName . "\n" . 
                  "Room: " . $roomName . "\n" .
                  "Room Type: " . $roomType . "\n" . 
                  "Asset Name: " . $uniqueName . "\n" . 
                  "Deployed to: " . $deployedTo . "\n" . // Make sure this is populated correctly
                  "Comment: " . $comments . "\n" . 
                  "Last Inspected: " . $lastInspected;

    // Generate QR code (Make sure the folder exists and is writable)
    $qrcodePath = '../qrcodes/' . $requestId . $categoryName . $subcategoryName . '.png';
    QRcode::png($qrcodeData, $qrcodePath); 

    // Convert QR code image to binary (Blob)
    $qrcodeBlob = file_get_contents($qrcodePath);

    // Now, update the inventory table with the QR code
    $query = "UPDATE inventory 
              SET qrcode = ? 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        $qrcodeBlob,
        $requestId
    ]);

    if ($stmt->rowCount()) {
        // Redirect back to the deployed_assets.php page
        header('Location: deployed_assets.php'); 
        exit();  // Make sure the script stops executing after the redirect
    } else {
        echo "Error updating asset QR code.";
    }
}
?>
