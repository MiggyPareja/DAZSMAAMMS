<?php
// fetch_requests.php
require __DIR__ . '/../includes/db.php';

// SQL query to fetch procurement requests data
$sql = "SELECT 
            inv.id,
            brands.brand_name,
            models.model_name,
            users.first_name,
            users.last_name,
            departments.name AS department_name,
            inv.created_at,
            inv.quantity,
            inv.status,
            inv.unit_cost,
            inv.specs,
            c.name as category_name,
            s.name as subcategory_name
        FROM inventory inv
        JOIN brands ON brands.brand_id = inv.brand_id
        JOIN models ON models.model_id = inv.model_id
        JOIN departments ON departments.department_id = inv.department_id
        JOIN users ON users.user_id = inv.requested_by
        JOIN categories c on c.category_id = inv.category_id
        JOIN subcategory s on s.subcategory_id = inv.subcategory_id
        WHERE inv.status = 'Pending'";

// Execute the query
$stmt = $conn->prepare($sql);
$stmt->execute();

// Fetch the data
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Inventory 
$inv_sql = "SELECT 
            inv.id,
            brands.brand_name,
            models.model_name,
            users.first_name,
            users.last_name,
            departments.name AS department_name,
            inv.created_at,
            inv.quantity,
            inv.status,
            inv.unit_cost,
            inv.specs,
            c.name as category_name,
            s.name as subcategory_name
        FROM inventory inv
        JOIN brands ON brands.brand_id = inv.brand_id
        JOIN models ON models.model_id = inv.model_id
        JOIN departments ON departments.department_id = inv.department_id
        JOIN users ON users.user_id = inv.requested_by
        JOIN categories c on c.category_id = inv.category_id
        JOIN subcategory s on s.subcategory_id = inv.subcategory_id
        WHERE inv.status = 'Approved'";

// Execute the query
$stmt = $conn->prepare($inv_sql);
$stmt->execute();

// Fetch the data
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch room
$roomquery = "SELECT * FROM `rooms` ORDER BY name";
$stmt = $conn->prepare($roomquery);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users
$userquery = "SELECT * FROM `users` WHERE users.status = 'Active'";
$stmt = $conn->prepare($userquery);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

//deployed assets
$deployedSQL = "SELECT 
            inv.id as invID,
            inv.name as inventoryName,
            brands.brand_name as brandName,
            models.model_name as modelName,
            rooms.name as roomName,
            users.first_name as firstName,
            users.last_name as lastName,
            inv.qrcode,
            inv.status
        FROM inventory inv
        JOIN brands ON brands.brand_id = inv.brand_id
        JOIN models ON models.model_id = inv.model_id
        JOIN departments ON departments.department_id = inv.department_id
        JOIN users ON users.id_number = inv.deployed_to
        JOIN categories c on c.category_id = inv.category_id
        JOIN subcategory s on s.subcategory_id = inv.subcategory_id
        JOIN rooms on rooms.room_id = inv.room_id
        WHERE inv.status = 'Deployed'"
        ;

// Execute the query
$stmt = $conn->prepare($deployedSQL);
$stmt->execute();

// Fetch the data
$deployed = $stmt->fetchAll(PDO::FETCH_ASSOC);

//disposed assets
$disposedSQL = "SELECT 
            inv.id as invId,
            inv.name as inventoryName,
            brands.brand_name as brandName,
            models.model_name as modelName,
            rooms.name as roomName,
            users.first_name as firstName,
            users.last_name as lastName,
            inv.qrcode,
            inv.status,
            inv.dispose_date as disposed_date
        FROM inventory inv
        JOIN brands ON brands.brand_id = inv.brand_id
        JOIN models ON models.model_id = inv.model_id
        JOIN departments ON departments.department_id = inv.department_id
        JOIN users ON users.user_id = inv.requested_by
        JOIN categories c on c.category_id = inv.category_id
        JOIN subcategory s on s.subcategory_id = inv.subcategory_id
        JOIN rooms on rooms.room_id = inv.room_id
        WHERE inv.status = 'Disposed'"
        ;

// Execute the query
$stmt = $conn->prepare($disposedSQL);
$stmt->execute();

// Fetch the data
$disposed = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
