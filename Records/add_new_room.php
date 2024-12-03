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

$room_types = fetchOptions('room_types', 'name');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // New entries if provided
    $new_room_type = $_POST['new_room_type'] ?? '';
    $new_room = $_POST['new_room'] ?? '';

    try {
        // Handle new room type creation
        if (!empty($new_room_type)) {
            // Check if the room type already exists
            $stmt = $conn->prepare("SELECT id FROM room_types WHERE name = :name");
            $stmt->bindParam(':name', $new_room_type);
            $stmt->execute();
            $room_type_id = $stmt->fetchColumn();

            if (!$room_type_id) {
                $stmt = $conn->prepare("INSERT INTO room_types (name) VALUES (:name)");
                $stmt->bindParam(':name', $new_room_type);
                $stmt->execute();
                $room_type_id = $conn->lastInsertId();
            }
        } else {
            $room_type_id = $_POST['room_type'];
            $stmt = $conn->prepare("SELECT COUNT(*) FROM room_types WHERE id = :id");
            $stmt->bindParam(':id', $room_type_id);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Invalid Input.");
            }
        }

 
        if (!empty($new_room)) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE name = :name AND room_type_id = :room_type_id");
            $stmt->bindParam(':name', $new_room);
            $stmt->bindParam(':room_type_id', $room_type_id);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $error = "Room '$new_room' already exists for the selected room type.";
            } else {
                $stmt = $conn->prepare("INSERT INTO rooms (name, room_type_id) VALUES (:name, :room_type_id)");
                $stmt->bindParam(':name', $new_room);
                $stmt->bindParam(':room_type_id', $room_type_id);
                $stmt->execute();
                echo '<script>alert("Room Added Successfully"); window.location.href = "add_new_room.php";</script>';
                exit();
            }
        } else {
            $room_id = $_POST['room'];
        }

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room - School Asset Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto bg-white p-8 mt-10 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">Add New Room</h2>
        
        <?php if (!empty($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p class="text-green-500 text-center mb-4"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="add_new_room.php" method="POST" id="roomForm" class="space-y-4">
            <div>
                <label for="room_type" class="block text-gray-700 font-medium">Room Type:</label>
                <select id="room_type" name="room_type" class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                    <option value="">Select Room Type</option>
                    <?php foreach ($room_types as $room_type): ?>
                        <option value="<?php echo htmlspecialchars($room_type['id']); ?>">
                            <?php echo htmlspecialchars($room_type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <input type="text" id="new_room_type" name="new_room_type" class="w-full border border-gray-300 rounded-lg p-2 mt-1" placeholder="Or enter a new room type">
            </div>

            <div>
                <label for="room" class="block text-gray-700 font-medium">Room:</label>
                <select id="room" name="room" class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                    <option value="">Select Room</option>
                </select>
            </div>

            <div>
                <input type="text" id="new_room" name="new_room" class="w-full border border-gray-300 rounded-lg p-2 mt-1" placeholder="Or enter a new room">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-all">Add Room</button>
            <a href="add_item.php" class="block text-center text-blue-500 hover:underline mt-4">Go Back</a>
        </form>
    </div>



    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const roomTypeSelect = document.getElementById('room_type');
        const roomSelect = document.getElementById('room');
        const form = document.getElementById('roomForm');

        roomTypeSelect.addEventListener('change', updateRooms);
        form.onsubmit = confirmAddition;

        function updateRooms() {
            const roomType = roomTypeSelect.value;
            roomSelect.innerHTML = '<option value="">Select Room</option>';

            if (roomType) {
                fetch('get_rooms.php?room_type=' + roomType)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(room => {
                            let option = new Option(room.name, room.id);
                            roomSelect.options.add(option);
                        });
                    })
                    .catch(error => console.error('Fetch error:', error));
            }
        }

        function confirmAddition() {
            return confirm("Are you sure you want to add this item?");
        }
    });
    </script>
</body>
</html>
