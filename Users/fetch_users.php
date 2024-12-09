<?php
// fetch_requests.php
require __DIR__ . '/../includes/db.php';

// SQL query to fetch procurement requests data
$sql = "SELECT * FROM users;";

// Execute the query
$stmt = $conn->prepare($sql);
$stmt->execute();

// Fetch the data
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if toggle_status is set (for toggling user status)
if (isset($_GET['toggle_status'])) {
    $userId = $_GET['toggle_status'];

    // Fetch the current status of the user
    $stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $status = $stmt->fetchColumn();

    // Toggle the status: If 'Active', set to 'Inactive', otherwise set to 'Active'
    $newStatus = ($status === 'Active') ? 'Inactive' : 'Active';

    // Update the user's status in the database
    $updateStmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $updateStmt->execute([$newStatus, $userId]);

    // Redirect back to the manage_users.php page
    header('Location: manage_users.php');
    exit();  // Ensure no further code executes after the redirect
}
?>

<td class="px-6 py-4">
    <a href="?toggle_status=<?= $user['user_id']; ?>" class="text-blue-500 hover:text-blue-700">
        <?= $user['status'] === 'Active' ? 'Deactivate' : 'Activate'; ?>
    </a>
</td>

?>