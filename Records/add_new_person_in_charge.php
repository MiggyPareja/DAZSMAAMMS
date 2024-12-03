<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success_message = '';

function fetchOptions($table, $column)
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, $column FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$persons_in_charge = fetchOptions('persons_in_charge', 'name');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_person_in_charge = $_POST['new_person_in_charge'] ?? '';

    try {
        // Check if the new person in charge already exists
        if (!empty($new_person_in_charge)) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM persons_in_charge WHERE name = :name");
            $stmt->bindParam(':name', $new_person_in_charge);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = "The person in charge '$new_person_in_charge' already exists.";
            } else {

                // Insert new person in charge
                $stmt = $conn->prepare("INSERT INTO persons_in_charge (name) VALUES (:name)");
                $stmt->bindParam(':name', $new_person_in_charge);
                $stmt->execute();
                $person_in_charge_id = $conn->lastInsertId();

$activity = "Add Person in Charge";
$stmt = $conn->prepare("INSERT INTO userlogs (userID, activity) VALUES (:userID, :activity)");
$stmt->bindParam(':userID', $user_id);
$stmt->bindParam(':activity', $activity);
$stmt->execute();
                echo '<script>alert("Person in charge added successfully."); window.location.href = "add_new_person_in_charge.php";</script>';
                exit();
            }
        } else {
            $person_in_charge_id = $_POST['person_in_charge'];
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Person In Charge | DAZSMA AMMS</title>
    <script>
    function confirmAddition() {
        return confirm("Are you sure you want to add this item?");
    }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Add New Person In Charge</h2>

        <?php if (!empty($error)): ?>
            <p class="text-red-500 mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p class="text-green-500 mb-4"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="add_new_person_in_charge.php" method="POST" onsubmit="return confirmAddition();" class="space-y-4">
            <div>
                <label for="person_in_charge" class="block text-gray-700 font-semibold mb-2">Person In Charge:</label>
                <select id="person_in_charge" name="person_in_charge" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Person In Charge</option>
                    <?php foreach ($persons_in_charge as $person_in_charge): ?>
                        <option value="<?php echo htmlspecialchars($person_in_charge['id']); ?>"><?php echo htmlspecialchars($person_in_charge['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="new_person_in_charge" class="block text-gray-700 font-semibold mb-2">Or Enter a New Person In Charge:</label>
                <input type="text" id="new_person_in_charge" name="new_person_in_charge" placeholder="Enter new person in charge" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">Add Item</button>
                <a href="add_item.php" class="text-gray-500 hover:text-gray-700">Go Back</a>
            </div>
        </form>
    </div>

 
</body>
</html>
