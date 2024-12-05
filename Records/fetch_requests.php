<?php
// fetch_requests.php
require __DIR__ . '/../includes/db.php';

// SQL query to fetch procurement requests data
$sql = "SELECT 
            pr.procurement_request_id,
            brands.brand_name,
            models.model_name,
            users.first_name,
            users.last_name,
            departments.name AS department_name,
            pr.request_date,
            pr.quantity,
            pr.status,
            pr.unit_cost,
            pr.specs
        FROM procurement_requests pr
        JOIN brands ON brands.brand_id = pr.brand
        JOIN models ON models.model_id = pr.model
        JOIN departments ON departments.department_id = pr.department
        JOIN users ON users.user_id = pr.person_in_charge_id
        WHERE pr.status = 'Pending'";

// Execute the query
$stmt = $conn->prepare($sql);
$stmt->execute();

// Fetch the data
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Inventory 
$inv_sql = "SELECT 
            pr.procurement_request_id,
            brands.brand_name,
            models.model_name,
            users.first_name,
            users.last_name,
            departments.name AS department_name,
            pr.request_date,
            pr.quantity,
            pr.status,
            pr.unit_cost,
            pr.specs
        FROM procurement_requests pr
        JOIN brands ON brands.brand_id = pr.brand
        JOIN models ON models.model_id = pr.model
        JOIN departments ON departments.department_id = pr.department
        JOIN users ON users.user_id = pr.person_in_charge_id
        WHERE pr.status = 'Approved' || 'Deployed'";

// Execute the query
$stmt = $conn->prepare($inv_sql);
$stmt->execute();

// Fetch the data
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
