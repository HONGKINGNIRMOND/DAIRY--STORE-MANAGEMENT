<?php
session_start();
require_once 'config/db.php';

// Get all products
$sql = "SELECT * FROM products";
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
    <title>Products - Fresh Dairy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>Fresh Dairy</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php" class="active">Products</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="products-section">
        <div class="container">
            <h2>Our Products</h2>
            <div class="product-grid">
                <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
                        <p><?php echo substr($product['description'], 0, 100); ?>...</p>
                        <p class="stock-info" style="color: <?php echo $product['quantity_in_stock'] > 10 ? '#27ae60' : ($product['quantity_in_stock'] > 0 ? '#f39c12' : '#e74c3c'); ?>; font-weight: bold; margin-bottom: 10px;">
                            <?php 
                                if($product['quantity_in_stock'] > 10) {
                                    echo "In Stock (" . $product['quantity_in_stock'] . " available)";
                                } elseif($product['quantity_in_stock'] > 0) {
                                    echo "Low Stock (" . $product['quantity_in_stock'] . " left)";
                                } else {
                                    echo "Out of Stock";
                                }
                            ?>
                        </p>
                        <div class="product-actions">
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                            <?php if(isset($_SESSION['user_id']) && $product['quantity_in_stock'] > 0): ?>
                                <form action="add-to-cart.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn">Add to Cart</button>
                                </form>
                            <?php elseif($product['quantity_in_stock'] <= 0): ?>
                                <button class="btn" disabled style="background-color: #ccc; cursor: not-allowed;">Out of Stock</button>
                            <?php else: ?>
                                <a href="login.php" class="btn">Login to Buy</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if(empty($products)): ?>
                    <p>No products available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 Fresh Dairy. All rights reserved.</p>
        </div>
    </footer>

<style>
@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)) !important;
    }
    
    .product-card {
        max-width: 100%;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .product-actions a, 
    .product-actions button,
    .product-actions form {
        width: 100%;
        margin-bottom: 10px;
        text-align: center;
    }
    
    header .container {
        flex-direction: column;
    }
    
    nav ul {
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 15px;
    }
    
    nav ul li {
        margin: 5px;
    }
}
</style>
</body>
</html>

