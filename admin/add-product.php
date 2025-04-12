<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

$error = '';
$success = '';

// Get all farmers for dropdown
$farmers_sql = "SELECT id, name FROM farmers ORDER BY name";
$farmers_result = $conn->query($farmers_sql);
$farmers = [];

if ($farmers_result->num_rows > 0) {
    while($row = $farmers_result->fetch_assoc()) {
        $farmers[] = $row;
    }
}

// Get all warehouses for dropdown
$warehouses_sql = "SELECT id, name FROM warehouses ORDER BY name";
$warehouses_result = $conn->query($warehouses_sql);
$warehouses = [];

if ($warehouses_result->num_rows > 0) {
    while($row = $warehouses_result->fetch_assoc()) {
        $warehouses[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $farmer_id = $_POST['farmer_id'] ? $_POST['farmer_id'] : NULL;
    $warehouse_id = $_POST['warehouse_id'] ? $_POST['warehouse_id'] : NULL;
    $quantity = $_POST['quantity'];
    
    // Handle image upload
    $image = 'default.jpg'; // Default image
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Check if the file type is allowed
        if(in_array(strtolower($filetype), $allowed)) {
            // Create unique filename
            $newname = uniqid() . '.' . $filetype;
            $target = '../images/products/' . $newname;
            
            // Move the uploaded file
            if(move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = $newname;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed);
        }
    }
    
    if(empty($error)) {
        // Insert product
        $sql = "INSERT INTO products (name, description, price, image, category, farmer_id, warehouse_id, quantity_in_stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssiis", $name, $description, $price, $image, $category, $farmer_id, $warehouse_id, $quantity);
        
        if ($stmt->execute()) {
            $success = "Product added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Add New Product</h2>
                <a href="products.php" class="btn">Back to Products</a>
            </div>
            
            <?php if($error != ''): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success != ''): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container" style="max-width: 800px; margin: 0;">
                <form action="add-product.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Milk">Milk</option>
                            <option value="Cheese">Cheese</option>
                            <option value="Curd">Curd</option>
                            <option value="Butter">Butter</option>
                        
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image">
                        <small>Leave empty to use default image</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="farmer_id">Farmer</label>
                        <select id="farmer_id" name="farmer_id">
                            <option value="">Select Farmer</option>
                            <?php foreach($farmers as $farmer): ?>
                                <option value="<?php echo $farmer['id']; ?>"><?php echo $farmer['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="warehouse_id">Warehouse</label>
                        <select id="warehouse_id" name="warehouse_id">
                            <option value="">Select Warehouse</option>
                            <?php foreach($warehouses as $warehouse): ?>
                                <option value="<?php echo $warehouse['id']; ?>"><?php echo $warehouse['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity in Stock</label>
                        <input type="number" id="quantity" name="quantity" min="0" required>
                    </div>
                    
                    <button type="submit" class="form-btn">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

