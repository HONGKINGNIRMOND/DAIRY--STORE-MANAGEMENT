<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Get all products with farmer and warehouse info
$sql = "SELECT p.*, f.name as farmer_name, w.name as warehouse_name 
        FROM products p 
        LEFT JOIN farmers f ON p.farmer_id = f.id 
        LEFT JOIN warehouses w ON p.warehouse_id = w.id 
        ORDER BY p.id DESC";
$result = $conn->query($sql);
$products = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Manage Products</h2>
                <a href="add-product.php" class="btn">Add New Product</a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Farmer</th>
                        <th>Warehouse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $product): ?>
                        <tr>
                            <td>#<?php echo $product['id']; ?></td>
                            <td>
                                <img src="../images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td><?php echo $product['name']; ?></td>
                            <td>₹<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['quantity_in_stock']; ?></td>
                            <td><?php echo $product['farmer_name'] ?? 'N/A'; ?></td>
                            <td><?php echo $product['warehouse_name'] ?? 'N/A'; ?></td>
                            <td>
                                <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <a href="delete-product.php?id=<?php echo $product['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<style>
@media (max-width: 768px) {
    .admin-container {
        grid-template-columns: 1fr !important;
    }
    
    .admin-sidebar {
        display: none;
    }
    
    .data-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-header a {
        margin-top: 10px;
    }
}
</style>
</body>
</html>

