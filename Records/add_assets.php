<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZSMA Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-cover bg-center h-screen"
    style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('images/Background.png');">
        <a>
        <div class="bg-blue-900 w-64 flex flex-col p-4 fixed h-full space-y-4 z-20">
              <div class="image">
                  <img src="images/SYSTEM LOGO 2.png" alt="User Image" class="text-white text-left">
              </div>
        </a>



        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
              <div class="image">
                  <img src="images/avatar.png" class="rounded-full w-12 h-12" alt="User Image">
              </div>
              <div class="info">
                  <a href="#" class="d-block text-white"><?php echo $username; ?></a>
              </div>
          </div>
            <nav class="flex flex-col space-y-4">
                <?php if ($role == 'Admin' || $role == 'Property Custodian' || $role == 'Inspector'): ?>
                    <!-- Dashboard -->
                    <a href="../dashboard.php">
                        <button class="nav-icon fas fa-tachometer-alt text-white text-sm"> Dashboard</button>             
                    </a>

                    <!-- Collapsible Records Section -->
                    <a>
                    <button id="recordsBtn" class="nav-icon fas fa-folder text-white text-sm"> Records
    <span id="arrow" class="transform transition-transform">&#9660;</span>
</button></a>
                    <div id="recordsMenu" class="hidden flex flex-col p-2 space-y-3">
                        <a href="view_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Assets</button>
                        </a>
                        <?php if ($role == 'Admin' || $role == 'Property Custodian'): ?>
                        <a href="dispose_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Disposed Assets</button>
                        </a>
                        <a href="add_assets.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Deploy Assets</button>
                        </a>
                        <?php endif; ?>
                        <a href="view_request.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> View Requests</button>
                        </a>
                        <a href="procurement.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Generate Request</button>
                        </a>
                    </div>
                    
                    <!-- Reports -->
                    <a>
                    <button id="reportsBtn" class="nav-icon fas fa-chart-bar text-white text-sm"> Reports
    <span id="arrow" class="transform transition-transform">&#9660;</span>
</button></a>
                    <div id="reportsMenu" class="hidden flex flex-col p-2 space-y-3">
                        <a href="reports.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Person-In-Charge</button>
                        </a>
                        <a href="asset_durability.php">
                            <button class="far fa-circle nav-icon text-white text-xs"> Asset Durability</button>
                        </a>
                    </div>

                    <!-- User Management for Admin Only -->
                    <?php if ($role == 'Admin'): ?>
                        <a href="../Users/manage_users.php">
                            <button class="nav-icon fas fa-id-card text-white text-sm"> User Management</button>
                        </a>
                    <?php endif; ?>

                    <!-- Log Out -->
                    <a href="../logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>

                <?php elseif ($role == 'Faculty'): ?>
                    <!-- Faculty Specific Options -->
                    <a href="reports.php">
                        <button class="nav-icon fas fa-folder text-white text-sm"> Reports</button>
                    </a>
                    <a href="procurement.php">
                        <button class="far fa-circle nav-icon text-white text-sm"> Generate Request</button>
                    </a>
                    <a href="logout.php" onclick="confirmLogout(event)">
                        <button class="nav-icon fas fa-sign-out-alt text-white text-sm"> Log Out</button>
                    </a>
                <?php endif; ?>
            </nav>
        </div>

<script>
        document.getElementById('recordsBtn').addEventListener('click', function () {
            const recordsMenu = document.getElementById('recordsMenu');
            const arrow = document.getElementById('arrow');
            recordsMenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });

        document.getElementById('reportsBtn').addEventListener('click', function () {
            const reportsMenu = document.getElementById('reportsMenu');
            const arrow = document.getElementById('arrow');
            reportsMenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });
</script>
      

    
        <div class="flex-1 flex flex-col items-center justify-center ml-64 top-0">
  
        <div class="bg-white rounded-3xl p-8 shadow-lg w-full max-w-xl">
        <?php if (!empty($error))
            echo '<p class="error">' . $error . '</p>'; ?>
      
    <h2 class="text-2xl font-bold mb-2">Deploy Asset</h2>
    

    <?php if (!empty($error)): ?>
        <p class="text-red-500 mb-2"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <p class="text-green-500 mb-2"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <form action="add_assets.php" method="POST" onsubmit="return confirmAddition();">
        <div class="mb-1">
            <label for="category" class="block text-sm font-semibold mb-1">Category:</label>
            <select id="category" name="category" required class="w-full p-1 border rounded-md">
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['user_id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-1">
            <label for="sub_category" class="block text-sm font-semibold mb-2">Sub-Category:</label>
            <select id="sub_category" name="sub_category" required class="w-full p-2 border rounded-md">
                <option value="">Select Sub-Category</option>
            </select>
        </div>
        <div class="mb-1 flex w-full">
        <div class="mb-1 w-full mr-2">
            <label for="room_type" class="block text-sm font-semibold mb-2">Room Type:</label>
            <select id="room_type" name="room_type" required class="w-full p-2 border rounded-md">
                <option value="">Select Room Type</option>
                <?php foreach ($room_types as $room_type): ?>
                    <option value="<?php echo htmlspecialchars($room_type['id']); ?>"><?php echo htmlspecialchars($room_type['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-1 w-full">
            <label for="room" class="block text-sm font-semibold mb-2">Room:</label>
            <select id="room" name="room" required class="w-full p-2 border rounded-md">
                <option value="">Select Room</option>
            </select>
        </div>

        </div>
       
        <div class="mb-1">
            <label for="asset" class="block text-sm font-semibold mb-2">Brand:</label>
            <select id="asset" name="asset" required class="w-full p-2 border rounded-md">
                <option value="">Select Brand</option>
            </select>
            
        </div>
    
        <div class="mb-1 flex  w-full ">
        <div class="mb-1  w-full mr-2">
            <label for="asset" class="block text-sm font-semibold mb-2">Model:</label>
            <input name="model" required class="w-full p-2 border rounded-md" placeholder="Enter Model">
              
        </div>
        <div class="mb-1 w-full">
            <label for="asset" class="block text-sm font-semibold mb-2">Specs:</label>
            <input name="specs" required class="w-full p-2 border rounded-md" placeholder="Enter Specs">
              
        </div>
        </div>
      
        <div class="mb-1">
            <label for="person_in_charge" class="block text-sm font-semibold mb-2">Person In Charge:</label>
            <select id="person_in_charge" name="person_in_charge" required class="w-full p-2 border rounded-md">
                <option value="">Select Person In Charge</option>
                <?php foreach ($persons_in_charge as $person): ?>
                    <option value="<?php echo htmlspecialchars($person['id']); ?>"><?php echo htmlspecialchars($person['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex items-center space-x-4">
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">Assign</button>
            <a href="add_item.php" class="text-blue-500 hover:underline">Add Item (if the item you want to choose does not exist)</a>
           
        </div>
    </form>
</div>

        </div>
        </div>
  
      
    </div>



</body>
