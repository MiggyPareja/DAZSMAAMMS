<?php
require '../includes/db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form</title>
    <script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
          @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

* {
    font-family: 'Poppins', sans-serif;
}
</style>
</head>

<body class="bg-cover bg-center h-screen" style="background-image: linear-gradient(110deg, rgba(32, 32, 146, 0.55) 100%, #202092 45%), url('../images/Background.png');">
        
<?php include '../sidebar.php'; ?>
<?php include './generateRequest.php'; ?>

      
<div class="flex-1 flex flex-col items-center justify-center ml-64 top-0 p-6">
    <div class="w-full max-w-2xl bg-white border border-gray-300 rounded-lg shadow-lg p-6">
        <form action="#" method="POST" class="space-y-6">
            <div class="space-y-4">
                
                <!-- Styled Request Title -->
                <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Request Form</h1> <!-- Enhanced Title -->
                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg">
                        <p class="font-medium">Data has been successfully inserted!</p>
                    </div>
                <?php endif; ?>
                <!-- Department Dropdown -->
                <div>
                    <label for="department" class="block text-gray-700 font-medium">Department:</label>
                    <select id="department" name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo htmlspecialchars($department['department_id']); ?>">
                                <?php echo htmlspecialchars($department['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="date" class="block text-gray-700 font-medium">Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="requestedBy" class="block text-gray-700 font-medium">Requested by:</label>
                    <select id="requestedBy" name="requestedBy" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Person In Charge</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                <?php echo htmlspecialchars($user['last_name'] . ',' . $user['first_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Brand and Model (Dropdowns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="brand" class="block text-gray-700 font-medium">Brand:</label>
                    <select id="brand" name="brand" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="fetchModels(this.value)" required>
                        <option value="">Select Brand</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand['brand_id']); ?>">
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="javascript:void(0);" 
                        class="text-blue-500 hover:text-blue-700" 
                        onclick="showModal('deploymentModal')">
                        Add Custom Brand and Models
                    </a>
                </div>
                <div>
                    <label for="model" class="block text-gray-700 font-medium">Model:</label>
                    <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Model</option>
                    </select>
                </div>
            </div>

            <!-- Quantity and Price (Grid layout for better spacing) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="quantity" class="block text-gray-700 font-medium">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" step="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="price" class="block text-gray-700 font-medium">Price:</label>
                    <input placeholder="Enter Price" type="text" name="price" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <!-- Specs (Textarea instead of input) -->
            <div>
                <label for="specs" class="block text-gray-700 font-medium">Specs:</label>
                <textarea id="specs" name="specs" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Specs" rows="6" style="resize: none;"></textarea>
            </div>

            <button type="submit" class="w-full py-2 bg-blue-500 text-white font-bold rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Submit Form</button>
        </form>
    </div> 
</div>

<!-- Deployment Modal -->
<div id="deploymentModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h2 class="text-xl font-bold mb-4">Deploy Asset</h2>
        <form id="deployForm">
            <input type="hidden" id="requestId" name="requestId">
            <div id="inputContainer">
                <div class="mb-4 input-group">
                    <label for="assetName" class="block font-medium">Asset Name</label>
                    <input type="text" name="assetName[]" class="w-full border rounded p-2">
                </div>
                <div class="mb-4 input-group">
                    <label for="roomId" class="block font-medium">Room ID</label>
                    <input type="text" name="roomId[]" class="w-full border rounded p-2">
                </div>
                <div class="mb-4 input-group">
                    <label for="personInChargeId" class="block font-medium">Person in Charge ID</label>
                    <input type="text" name="personInChargeId[]" class="w-full border rounded p-2">
                </div>
            </div>
            <div class="flex justify-between items-center mb-4">
                <button type="button" onclick="addInputGroup()" class="bg-blue-500 text-white px-4 py-2 rounded">Add Another One</button>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded" onclick="closeModal('deploymentModal')">Cancel</button>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Deploy</button>
            </div>
        </form>
    </div>
</div>

<!-- Script to Handle Modal and Dynamic Input -->
<script>
    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function addInputGroup() {
        const inputContainer = document.getElementById('inputContainer');

        // Create a new input group
        const newInputGroup = document.createElement('div');
        newInputGroup.classList.add('mb-4', 'input-group');
        newInputGroup.innerHTML = `
            <div class="mb-4">
                <label class="block font-medium">Asset Name</label>
                <input type="text" name="assetName[]" class="w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium">Room ID</label>
                <input type="text" name="roomId[]" class="w-full border rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium">Person in Charge ID</label>
                <input type="text" name="personInChargeId[]" class="w-full border rounded p-2">
            </div>
        `;
        inputContainer.appendChild(newInputGroup);
    }
</script>




<script>
   
   function fetchModels(brandId) {
    if (brandId == "") {
        document.getElementById("model").innerHTML = "<option value=''>Select Model</option>";
        return;
    }

    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_models.php?brand_id=" + brandId, true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var models = JSON.parse(xhr.responseText);
            var modelSelect = document.getElementById("model");
            modelSelect.innerHTML = "<option value=''>Select Model</option>"; // Clear existing models
            models.forEach(function(model) {
                var option = document.createElement("option");
                option.value = model.model_id;
                option.textContent = model.model_name;
                modelSelect.appendChild(option);
            });
        }
    };
    xhr.send();
}


</script>


</body>

</html>
