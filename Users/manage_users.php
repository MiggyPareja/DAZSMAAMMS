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
    <?php include './edit_user.php'; ?>
    <?php include './add_user.php'; ?>

    <!-- Manage Users Section -->
    <div class="flex-1 ml-64 p-5">
        <div>
            <h2 class="text-white text-2xl font-bold mb-4">Manage Users</h2>
            
            <!-- Button to Open Add User Modal -->
            <button id="openModal" class="inline-block bg-blue-500 text-white py-2 px-4 rounded-md mb-4">Add New User</button>

            <!-- User Table -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg p-6">
                <table id="userTable" class="min-w-full border-separate border-spacing-y-3 display">
                    <thead class="bg-blue-500 text-white uppercase text-sm m-5">
                        <tr>
                            <th class="px-8 py-4 border-b">ID Number</th>
                            <th class="px-8 py-4 border-b">Username</th>
                            <th class="px-8 py-4 border-b">Email</th>
                            <th class="px-8 py-4 border-b">Role</th>
                            <th class="px-8 py-4 border-b">Status</th>
                            <th class="px-8 py-4 border-b">First Name</th>
                            <th class="px-8 py-4 border-b">Last Name</th>
                            <th class="px-8 py-4 border-b">Contact Number</th>
                            <th class="px-8 py-4 border-b">Birthdate</th>
                            <th class="px-8 py-4 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) { ?>
                            <tr class="bg-gray-50 hover:bg-gray-100 rounded-lg shadow-md">
                                <td class="px-8 py-6 border-b text-base text-gray-800 font-medium"><?= htmlspecialchars($user['id_number']); ?></td>
                                <td class="px-8 py-6 border-b text-base text-gray-800 font-medium"><?= htmlspecialchars($user['username']); ?></td>
                                <td class="px-8 py-6 border-b text-base text-gray-800"><?= htmlspecialchars($user['email']); ?></td>
                                <td class="px-8 py-6 border-b text-base text-gray-800"><?= htmlspecialchars($user['role']); ?></td>
                                <td class="px-8 py-6 border-b text-base">
                                    <span class="px-3 py-1 inline-block rounded-full 
                                        <?= $user['status'] == 'Active' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                        <?= htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6 border-b text-base text-gray-800"><?= htmlspecialchars($user['first_name']); ?></td>
                                <td class="px-8 py-6 border-b text-base text-gray-800"><?= htmlspecialchars($user['last_name']); ?></td>
                                <td class="px-8 py-6 border-b text-base text-gray-800"><?= htmlspecialchars($user['contact_number']); ?></td>
                                <td class="px-8 py-6 border-b text-base text-gray-800"><?= htmlspecialchars($user['birthdate']); ?></td>
                                <td class="px-8 py-6 border-b text-base">
                                    <button class="text-yellow-500 hover:text-yellow-700 edit-button" data-user="<?= htmlspecialchars(json_encode($user)); ?>">Edit</button> |
                                    <a href="delete_user.php?id=<?= htmlspecialchars($user['id_number']); ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a> |
                                    <?php if ($user['status'] == 'Inactive') { ?>
                                        <a href="reactivate_user.php?id=<?= htmlspecialchars($user['id_number']); ?>" class="text-green-500 hover:text-green-700" onclick="return confirm('Are you sure you want to reactivate this user?')">Reactivate</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Add User Modal HTML (Modified for Landscape Layout) -->
            <div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
                <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-7xl">
                    <h2 class="text-2xl font-semibold mb-6 text-center border-b pb-4 text-gray-800">Add New User</h2>
                    <form action="add_user.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-3 gap-8">
                        <!-- Left Column -->
                        <div class="col-span-1">
                            <div class="mb-6">
                                <label for="id_number" class="block text-sm font-medium text-gray-700">ID Number</label>
                                <input type="text" id="id_number" name="id_number" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="mb-6">
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" id="username" name="username" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="mb-6">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" name="email" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="mb-6">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" id="password" name="password" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </div>

                        <!-- Center Column -->
                        <div class="col-span-1">
                            <div class="mb-6">
                                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                <select id="role" name="role" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="Admin">Admin</option>
                                    <option value="Inspector">Inspector</option>
                                    <option value="Faculty">Faculty</option>
                                </select>
                            </div>
                            <div class="mb-6">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-6">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="mb-6">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-span-1">
                            <div class="mb-6">
                                <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                <input type="text" id="contact_number" name="contact_number" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="mb-6">
                                <label for="birthdate" class="block text-sm font-medium text-gray-700">Birthdate</label>
                                <input type="date" id="birthdate" name="birthdate" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <!-- Profile Picture Section -->
                            <div class="mb-6">
                                <label for="profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="mt-1 w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <!-- Profile picture preview -->
                                <img id="profile_picture_preview" class="mt-3 w-32 h-32 object-cover rounded-full hidden" src="" alt="Profile Picture Preview">
                            </div>
                        </div>

                        <!-- Buttons (Full Width) -->
                        <div class="col-span-3 flex justify-end mt-6 space-x-4">
                            <button type="button" id="closeModal" class="bg-gray-500 text-white py-2 px-6 rounded-lg hover:bg-gray-600">Cancel</button>
                            <button type="submit" class="bg-blue-500 text-white py-2 px-6 rounded-lg hover:bg-blue-600">Add User</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div id="editUserModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        <form id="editUserForm" method="POST" action="edit_user.php" enctype="multipart/form-data">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit User</h3>
                                        <div class="mt-2 grid grid-cols-2 gap-4">
                                            <input type="hidden" id="edit_user_id" name="id_number">
                                            <div class="mb-4">
                                                <label for="edit_username" class="block text-sm font-medium text-gray-700">Username</label>
                                                <input type="text" id="edit_username" name="username" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                                                <input type="email" id="edit_email" name="email" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_password" class="block text-sm font-medium text-gray-700">Password</label>
                                                <input type="password" id="edit_password" name="password" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_role" class="block text-sm font-medium text-gray-700">Role</label>
                                                <select id="edit_role" name="role" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                                    <option value="Admin">Admin</option>
                                                    <option value="Inspector">Inspector</option>
                                                    <option value="Faculty">Faculty</option>
                                                </select>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                                                <select id="edit_status" name="status" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                                <input type="text" id="edit_first_name" name="first_name" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                                <input type="text" id="edit_last_name" name="last_name" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                                <input type="text" id="edit_contact_number" name="contact_number" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_birthdate" class="block text-sm font-medium text-gray-700">Birthdate</label>
                                                <input type="date" id="edit_birthdate" name="birthdate" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                            </div>
                                            <div class="mb-4">
                                                <label for="edit_profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                                                <input type="file" id="edit_profile_picture" name="profile_picture" accept="image/*" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                <!-- Profile picture preview -->
                                                <img id="edit_profile_picture_preview" class="mt-3 w-32 h-32 object-cover rounded-full hidden" src="" alt="Profile Picture Preview">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm" id="closeEditModal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#userTable').DataTable();

        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('userModal').classList.add('hidden');
        });

        // Open the Add User Modal
        document.getElementById('openModal').addEventListener('click', function() {
            document.getElementById('userModal').classList.remove('hidden');
        });

        // Bind the edit button event to open the modal
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function() {
                const user = JSON.parse(this.getAttribute('data-user'));
                openEditUserModal(user);
            });
        });

        // Close the Edit User Modal
        document.getElementById('closeEditModal').addEventListener('click', function() {
            document.getElementById('editUserModal').classList.add('hidden');
        });

        // Profile picture preview for Add User Modal
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('profile_picture_preview');
                preview.src = reader.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        // Profile picture preview for Edit User Modal
        document.getElementById('edit_profile_picture').addEventListener('change', function(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('edit_profile_picture_preview');
                preview.src = reader.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    });

    function openEditUserModal(user) {
        document.getElementById('edit_user_id').value = user.id_number;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_status').value = user.status;
        document.getElementById('edit_first_name').value = user.first_name;
        document.getElementById('edit_last_name').value = user.last_name;
        document.getElementById('edit_contact_number').value = user.contact_number;
        document.getElementById('edit_birthdate').value = user.birthdate;
        document.getElementById('edit_profile_picture_preview').src = user.profile_picture;
        document.getElementById('edit_profile_picture_preview').classList.remove('hidden');
        document.getElementById('editUserModal').classList.remove('hidden');
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }
    </script>
</body>

</html>
