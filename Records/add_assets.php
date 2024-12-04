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

<?php include '../sidebar.php'; ?>

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
