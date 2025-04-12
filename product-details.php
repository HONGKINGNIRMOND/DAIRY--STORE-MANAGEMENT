<?php
session_start();
require_once 'config/db.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// Get product details
$sql = "SELECT p.*, f.name as farmer_name 
        FROM products p 
        LEFT JOIN farmers f ON p.farmer_id = f.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Product not found
    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// Get related products
$related_sql = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("si", $product['category'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_products = [];

if ($related_result->num_rows > 0) {
    while($row = $related_result->fetch_assoc()) {
        $related_products[] = $row;
    }
}
$related_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Fresh Dairy</title>
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

    <section class="product-details">
        <div class="container">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px; background-color: #ffeeee; padding: 10px; border-radius: 4px;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="product-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin: 40px 0;">
                <div class="product-image">
                    <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width: 100%; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                </div>
                <div class="product-info">
                    <h1><?php echo $product['name']; ?></h1>
                    <p class="price" style="font-size: 24px; color: #27ae60; font-weight: bold; margin: 15px 0;">₹<?php echo number_format($product['price'], 2); ?></p>
                    
                    <div class="product-description" style="margin: 20px 0;">
                        <h3>Description</h3>
                        <p><?php echo $product['description']; ?></p>
                    </div>
                    
                    <div class="product-meta" style="margin: 20px 0;">
                        <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
                        <?php if($product['farmer_name']): ?>
                            <p><strong>Sourced from:</strong> <?php echo $product['farmer_name']; ?></p>
                        <?php endif; ?>
                        <p><strong>Availability:</strong> 
                            <?php if($product['quantity_in_stock'] > 20): ?>
                                <span style="color: #27ae60;">In Stock</span>
                            <?php elseif($product['quantity_in_stock'] > 0): ?>
                                <span style="color: #f39c12;">Limited Stock (<?php echo $product['quantity_in_stock']; ?> left)</span>
                            <?php else: ?>
                                <span style="color: #e74c3c;">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php if($product['quantity_in_stock'] > 0 && isset($_SESSION['user_id'])): ?>
                        <form action="add-to-cart.php" method="post" style="margin: 20px 0;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                                <label for="quantity" style="margin-right: 10px;"><strong>Quantity:</strong></label>
                                <select name="quantity" id="quantity" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                                    <?php for($i = 1; $i <= min(10, $product['quantity_in_stock']); $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn" style="width: 100%;">Add to Cart</button>
                        </form>
                    <?php elseif(!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn" style="display: block; text-align: center; margin: 20px 0;">Login to Buy</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if(!empty($related_products)): ?>
                <div class="related-products" style="margin: 60px 0;">
                    <h2>Related Products</h2>
                    <div class="product-grid">
                        <?php foreach($related_products as $related): ?>
                            <div class="product-card">
                                <img src="images/products/<?php echo $related['image']; ?>" alt="<?php echo $related['name']; ?>">
                                <h3><?php echo $related['name']; ?></h3>
                                <p class="price">₹<?php echo number_format($related['price'], 2); ?></p>
                                <p><?php echo substr($related['description'], 0, 100); ?>...</p>
                                <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn">View Details</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 Fresh Dairy. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

