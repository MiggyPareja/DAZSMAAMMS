<?php
// Assuming you're fetching departments from the database to populate the department dropdown
// You may need to adjust this part based on your department table and database query
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Font -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- jQuery and DataTables JS -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</head>

<body class="bg-cover bg-center h-screen">
    <?php include '../sidebar.php'; ?>
    <?php include './fetch_users.php'; ?>

    <!-- Manage Users Section -->
    <div class="flex-1 ml-64 p-4">
        <div>
            <h2 class="text-white text-2xl font-bold mb-4">Manage Users</h2>
            
            <!-- Button to Open Add User Modal -->
            <button id="openModal" class="inline-block bg-blue-500 text-white py-2 px-4 rounded-md mb-4">Add New User</button>

            <!-- User Table -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table id="usersTable" class="min-w-full table-auto">
                    <thead class="bg-white text-black">
                        <tr>
                            <th class="px-6 py-3 text-left">ID Number</th>
                            <th class="px-6 py-3 text-left">Username</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Role</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $user) {  // Modify according to how you fetch users
                        ?>
                        <tr>
                            <td class="px-6 py-4"><?= $user['id_number']; ?></td>
                            <td class="px-6 py-4"><?= $user['username']; ?></td>
                            <td class="px-6 py-4"><?= $user['email']; ?></td>
                            <td class="px-6 py-4"><?= $user['role']; ?></td>
                            <td class="px-6 py-4"><?= $user['status']; ?></td>
                            <td class="px-6 py-4">
                                <a href="edit_user.php?id=<?= $user['user_id']; ?>" class="text-yellow-500 hover:text-yellow-700">Edit(Under Construction)</a> |
                                <a href="delete_user.php?id=<?= $user['user_id']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this user?')">Delete(Under Construction)</a> |
                                <?php if ($user['status'] == 'Inactive') { ?>
                                    <a href="reactivate_user.php?id=<?= $user['user_id']; ?>" class="text-green-500 hover:text-green-700" onclick="return confirm('Are you sure you want to reactivate this user?')">Reactivate(Under Construction)</a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Add User Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg p-6 w-1/3">
        <h2 class="text-xl font-semibold mb-4">Add New User</h2>
        <form action="add_user.php" method="POST">
            <!-- ID Number Field -->
            <div class="mb-4">
                <label for="id_number" class="block text-sm">ID Number</label>
                <input type="text" id="id_number" name="id_number" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="username" class="block text-sm">Username</label>
                <input type="text" id="username" name="username" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm">Email</label>
                <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
            <!-- Password field -->
            <div class="mb-4">
                <label for="password" class="block text-sm">Password</label>
                <input type="password" id="password" name="password" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="role" class="block text-sm">Role</label>
                <select id="role" name="role" class="w-full p-2 border border-gray-300 rounded-md" required>
                    <option value="Admin">Admin</option>
                    <option value="Inspector">Inspector</option>
                    <option value="Faculty">Faculty</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="status" class="block text-sm">Status</label>
                <select id="status" name="status" class="w-full p-2 border border-gray-300 rounded-md" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <!-- New fields based on the schema -->
            <div class="mb-4">
                <label for="first_name" class="block text-sm">First Name</label>
                <input type="text" id="first_name" name="first_name" class="w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="last_name" class="block text-sm">Last Name</label>
                <input type="text" id="last_name" name="last_name" class="w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="contact_number" class="block text-sm">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" class="w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="birthdate" class="block text-sm">Birthdate</label>
                <input type="date" id="birthdate" name="birthdate" class="w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="flex justify-between items-center">
                <button type="button" class="bg-gray-500 text-white py-2 px-4 rounded-md" id="closeModal">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md">Add User</button>
            </div>
        </form>
    </div>
</div>


    <!-- DataTables Initialization -->
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable({
                "paging": true, // Enable pagination
                "searching": true, // Enable search functionality
                "ordering": true, // Enable sorting functionality
                "info": true, // Show table info (e.g., "Showing 1 to 10 of 50 entries")
            });

            // Open modal when the button is clicked
            $('#openModal').click(function() {
                $('#userModal').removeClass('hidden');
            });

            // Close modal when the close button is clicked
            $('#closeModal').click(function() {
                $('#userModal').addClass('hidden');
            });
        });
    </script>

</body>

</html>
