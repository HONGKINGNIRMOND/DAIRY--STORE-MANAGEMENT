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

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

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

// Get product details
$product_sql = "SELECT * FROM products WHERE id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows == 0) {
    header("Location: products.php");
    exit();
}

$product = $product_result->fetch_assoc();
$product_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $farmer_id = $_POST['farmer_id'] ? $_POST['farmer_id'] : NULL;
    $warehouse_id = $_POST['warehouse_id'] ? $_POST['warehouse_id'] : NULL;
    $quantity = $_POST['quantity'];
    
    // Handle image upload
    $image = $product['image']; // Keep existing image by default
    
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
        // Update product
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, category = ?, 
                farmer_id = ?, warehouse_id = ?, quantity_in_stock = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssiisi", $name, $description, $price, $image, $category, $farmer_id, $warehouse_id, $quantity, $product_id);
        
        if ($stmt->execute()) {
            $success = "Product updated successfully!";
            
            // Refresh product data
            $product_stmt = $conn->prepare($product_sql);
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            $product = $product_result->fetch_assoc();
            $product_stmt->close();
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
    <title>Edit Product - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Edit Product</h2>
                <a href="products.php" class="btn">Back to Products</a>
            </div>
            
            <?php if($error != ''): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success != ''): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container" style="max-width: 800px; margin: 0;">
                <form action="edit-product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?php echo $product['description']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Milk" <?php echo $product['category'] == 'Milk' ? 'selected' : ''; ?>>Milk</option>
                            <option value="Cheese" <?php echo $product['category'] == 'Cheese' ? 'selected' : ''; ?>>Cheese</option>
                            <option value="Yogurt" <?php echo $product['category'] == 'Yogurt' ? 'selected' : ''; ?>>Yogurt</option>
                            <option value="Butter" <?php echo $product['category'] == 'Butter' ? 'selected' : ''; ?>>Butter</option>
                            <option value="Cream" <?php echo $product['category'] == 'Cream' ? 'selected' : ''; ?>>Cream</option>
                            <option value="Ice Cream" <?php echo $product['category'] == 'Ice Cream' ? 'selected' : ''; ?>>Ice Cream</option>
                            <option value="Other" <?php echo $product['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <?php if($product['image'] && $product['image'] != 'default.jpg'): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="../images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image">
                        <small>Leave empty to keep current image</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="farmer_id">Farmer</label>
                        <select id="farmer_id" name="farmer_id">
                            <option value="">Select Farmer</option>
                            <?php foreach($farmers as $farmer): ?>
                                <option value="<?php echo $farmer['id']; ?>" <?php echo $product['farmer_id'] == $farmer['id'] ? 'selected' : ''; ?>><?php echo $farmer['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="warehouse_id">Warehouse</label>
                        <select id="warehouse_id" name="warehouse_id">
                            <option value="">Select Warehouse</option>
                            <?php foreach($warehouses as $warehouse): ?>
                                <option value="<?php echo $warehouse['id']; ?>" <?php echo $product['warehouse_id'] == $warehouse['id'] ? 'selected' : ''; ?>><?php echo $warehouse['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity in Stock</label>
                        <input type="number" id="quantity" name="quantity" min="0" value="<?php echo $product['quantity_in_stock']; ?>" required>
                    </div>
                    
                    <button type="submit" class="form-btn">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

