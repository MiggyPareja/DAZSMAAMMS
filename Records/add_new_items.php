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

$categories = fetchOptions('categories', 'name');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_category = $_POST['new_category'] ?? '';
    $new_sub_category = $_POST['new_sub_category'] ?? '';
    $new_asset = $_POST['new_asset'] ?? '';

    try {
        if (!empty($new_category)) {
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = :name");
            $stmt->bindParam(':name', $new_category);
            $stmt->execute();
            $category_id = $stmt->fetchColumn();

            if (!$category_id) {
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (:name)");
                $stmt->bindParam(':name', $new_category);
                $stmt->execute();
                $category_id = $conn->lastInsertId();
            }
        } else {
            $category_id = $_POST['category'];

            $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $category_id);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Invalid input.");
            }
        }

        if (!empty($new_sub_category)) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM sub_categories WHERE name = :name AND category_id = :category_id");
            $stmt->bindParam(':name', $new_sub_category);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $error = "Sub-Category '$new_sub_category' already exists for the selected category.";
            } else {
                $stmt = $conn->prepare("INSERT INTO sub_categories (name, category_id) VALUES (:name, :category_id)");
                $stmt->bindParam(':name', $new_sub_category);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->execute();
                $sub_category_id = $conn->lastInsertId();
            }
        } else {
            $sub_category_id = $_POST['sub_category'];

            $stmt = $conn->prepare("SELECT COUNT(*) FROM sub_categories WHERE id = :id AND category_id = :category_id");
            $stmt->bindParam(':id', $sub_category_id);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Invalid Sub-Category selected.");
            }
        }

        if (!empty($new_asset)) {
            $stmt = $conn->prepare("INSERT INTO assets (name, category_id, sub_category_id) VALUES (:name, :category_id, :sub_category_id)");
            $stmt->bindParam(':name', $new_asset);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':sub_category_id', $sub_category_id);
            $stmt->execute();
            echo '<script>alert("Item Added Successfully"); window.location.href = "add_new_items.php";</script>';
            exit();
        } else {
            $asset_id = $_POST['asset'];
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
    <title>Add New Items - School Asset Management</title>
    <script>
    function updateSubCategories() {
        const category = document.getElementById('category').value;
        const subCategorySelect = document.getElementById('sub_category');
        const assetSelect = document.getElementById('asset');

        subCategorySelect.innerHTML = '<option value="">Select Sub-Category</option>';
        assetSelect.innerHTML = '<option value="">Select Asset</option>';

        if (category) {
            fetch('get_sub_categories.php?category=' + category)
                .then(response => response.json())
                .then(data => {
                    data.forEach(subCat => {
                        let option = new Option(subCat.name, subCat.id);
                        subCategorySelect.options.add(option);
                    });
                })
                .catch(error => console.error('Fetch error:', error));
        }
    }

    function updateAssets() {
        const category = document.getElementById('category').value;
        const subCategory = document.getElementById('sub_category').value;
        const assetSelect = document.getElementById('asset');

        if (category && subCategory) {
            fetch('get_assets.php?category=' + category + '&sub_category=' + subCategory)
                .then(response => response.json())
                .then(data => {
                    data.forEach(asset => {
                        let option = new Option(asset.name, asset.id);
                        assetSelect.options.add(option);
                    });
                })
                .catch(error => console.error('Fetch error:', error));
        }
    }

    function confirmAddition() {
        return confirm("Are you sure you want to add this item?");
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('category').addEventListener('change', updateSubCategories);
        document.getElementById('sub_category').addEventListener('change', updateAssets);
    });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-6 bg-white shadow-md w-1/2 rounded-lg">
        <h2 class="text-2xl font-bold mb-5">Add New Items</h2>
        <?php if (!empty($error)): ?>
            <p class="text-red-600"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p class="text-green-600"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <form action="add_new_items.php" method="POST" onsubmit="return confirmAddition();">
            <div class="mb-4">
                <label for="category" class="block text-gray-700">Category:</label>
                <select id="category" name="category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <input type="text" id="new_category" name="new_category" placeholder="Or enter a new category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>

            <div class="mb-4">
                <label for="sub_category" class="block text-gray-700">Sub-Category:</label>
                <select id="sub_category" name="sub_category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">Select Sub-Category</option>
                </select>
            </div>

            <div class="mb-4">
                <input type="text" id="new_sub_category" name="new_sub_category" placeholder="Or enter a new sub-category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>

            <div class="mb-4">
                <label for="asset" class="block text-gray-700">Brand:</label>
                <select id="asset" name="asset" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">Select Brand</option>
                </select>
            </div>

            <div class="mb-4">
                <input type="text" id="new_asset" name="new_asset" placeholder="Or enter a new brand" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>

            <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Item</button>
            <a href="add_item.php" class="w-full block text-center py-2 px-4 mt-4 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Go Back</a>
        </form>
    </div>
    <div class="text-center mt-6">
        <p class="text-sm text-gray-500">&copy; School Asset Management. All rights reserved.</p>
    </div>
</body>
</html>
