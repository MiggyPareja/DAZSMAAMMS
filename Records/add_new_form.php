<?php
require 'includes/db.php';

$type = $_GET['type'] ?? '';

if ($type) {
    switch ($type) {
        case 'category':
            echo '<form action="add_category.php" method="POST">
                    <label for="new_category">New Category:</label>
                    <input type="text" id="new_category" name="name" required>
                    <button type="submit">Add</button>
                  </form>';
            break;

        case 'sub_category':
            echo '<form action="add_sub_category.php" method="POST">
                    <label for="new_sub_category">New Sub-Category:</label>
                    <input type="text" id="new_sub_category" name="name" required>
                    <label for="parent_category">Category:</label>
                    <select id="parent_category" name="category_id" required>
                        <option value="">Select Category</option>';
            // Fetch categories for dropdown
            $stmt = $conn->query('SELECT id, name FROM categories');
            foreach ($stmt as $row) {
                echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
            }
            echo '   </select>
                    <button type="submit">Add</button>
                  </form>';
            break;

        case 'room_type':
            echo '<form action="add_room_type.php" method="POST">
                    <label for="new_room_type">New Room Type:</label>
                    <input type="text" id="new_room_type" name="name" required>
                    <button type="submit">Add</button>
                  </form>';
            break;

        case 'room':
            echo '<form action="add_room.php" method="POST">
                    <label for="new_room">New Room:</label>
                    <input type="text" id="new_room" name="name" required>
                    <label for="room_type">Room Type:</label>
                    <select id="room_type" name="room_type_id" required>
                        <option value="">Select Room Type</option>';
            // Fetch room types for dropdown
            $stmt = $conn->query('SELECT id, name FROM room_types');
            foreach ($stmt as $row) {
                echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
            }
            echo '   </select>
                    <button type="submit">Add</button>
                  </form>';
            break;

        case 'asset':
            echo '<form action="add_asset.php" method="POST">
                    <label for="new_asset">New Asset:</label>
                    <input type="text" id="new_asset" name="name" required>
                    <label for="category">Category:</label>
                    <select id="category" name="category_id" required>
                        <option value="">Select Category</option>';
            // Fetch categories for dropdown
            $stmt = $conn->query('SELECT id, name FROM categories');
            foreach ($stmt as $row) {
                echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
            }
            echo '   </select>
                    <label for="sub_category">Sub-Category:</label>
                    <select id="sub_category" name="sub_category_id" required>
                        <option value="">Select Sub-Category</option>';
            // Fetch sub-categories for dropdown
            $stmt = $conn->query('SELECT id, name FROM sub_categories');
            foreach ($stmt as $row) {
                echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
            }
            echo '   </select>
                    <button type="submit">Add</button>
                  </form>';
            break;

        case 'person_in_charge':
            echo '<form action="add_person_in_charge.php" method="POST">
                    <label for="new_person_in_charge">New Person In Charge:</label>
                    <input type="text" id="new_person_in_charge" name="name" required>
                    <button type="submit">Add</button>
                  </form>';
            break;
    }
}
?>
