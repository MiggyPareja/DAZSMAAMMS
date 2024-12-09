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
<?php include './fetch_subcategories.php'; ?>

      
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Category Dropdown -->
                        <div>
                            <label for="category" class="block text-gray-700 font-medium">Category:</label>
                            <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="fetchSubcategories(this.value)" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Subcategory Dropdown -->
                        <div>
                            <label for="subcategory" class="block text-gray-700 font-medium">Subcategory:</label>
                            <select id="subcategory" name="subcategory" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Subcategory</option>
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
                    <a href="../dashboard.php" class="text-blue-500 hover:text-blue-700">Add Custom Brand and Models</a>
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
                <textarea id="specs" name="specs" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Specs" rows="6" style="resize: none; "></textarea>
            </div>

            <button type="submit" class="w-full py-2 bg-blue-500 text-white font-bold rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Submit Form</button>
        </form>
    </div>
</div>




<script>
   
   function fetchModels(brandId) {
    if (brandId == "") {
        document.getElementById("model").innerHTML = "<option value=''>Select Model</option>";
        return;
    }

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

 // Fetch subcategories based on selected category
function fetchSubcategories(categoryId) {
    const subcategoryDropdown = document.getElementById('subcategory');
    subcategoryDropdown.innerHTML = '<option value="">Loading...</option>'; // Show loading message
    
    console.log("Fetching subcategories for categoryId:", categoryId); // Debugging line
    
    // Fetch subcategories from the server via AJAX (using fetch API)
    fetch('fetch_subcategories.php?category_id=' + categoryId)
        .then(response => response.json())
        .then(data => {
            console.log("Subcategories data received:", data); // Debugging line

            // Clear the subcategory dropdown
            subcategoryDropdown.innerHTML = '<option value="">Select Subcategory</option>';
            
            // Check if the data is not empty
            if (data.length > 0) {
                // Populate the subcategory dropdown with options
                data.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.subcategory_id;
                    option.textContent = subcategory.name;
                    subcategoryDropdown.appendChild(option);
                });
            } else {
                subcategoryDropdown.innerHTML = '<option value="">No subcategories available</option>';
            }
        })
        .catch(error => {
            console.error('Error fetching subcategories:', error);
            subcategoryDropdown.innerHTML = '<option value="">Failed to load subcategories</option>';
        });
}

// Event listener for category change
document.getElementById('category').addEventListener('change', function () {
    const categoryId = this.value;
    console.log("Category changed, selected categoryId:", categoryId); // Debugging line
    if (categoryId) {
        fetchSubcategories(categoryId);
    } else {
        document.getElementById('subcategory').innerHTML = '<option value="">Select Subcategory</option>';
    }
});


</script>


</body>

</html>
