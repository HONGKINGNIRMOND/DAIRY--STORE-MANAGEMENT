<?php
session_start();
require_once 'config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total = 0;

// Get cart items
$sql = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Fresh Dairy</title>
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
                    <li><a href="products.php">Products</a></li>
                    <li><a href="cart.php" class="active">Cart</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="cart-container">
        <div class="container">
            <h2>Your Shopping Cart</h2>
            
            <?php if(empty($cart_items)): ?>
                <div style="text-align: center; margin: 50px 0;">
                    <p>Your cart is empty.</p>
                    <a href="products.php" class="btn" style="margin-top: 20px;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <div>
                        <?php foreach($cart_items as $item): ?>
                            <div class="cart-item">
                                <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                <div class="cart-item-details">
                                    <h3><?php echo $item['name']; ?></h3>
                                    <p class="cart-item-price">₹<?php echo number_format($item['price'], 2); ?></p>
                                    <div class="cart-item-quantity">
                                        <form action="update-cart.php" method="post" style="display: flex; align-items: center;">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="action" value="decrease">-</button>
                                            <span><?php echo $item['quantity']; ?></span>
                                            <button type="submit" name="action" value="increase">+</button>
                                            <button type="submit" name="action" value="remove" style="margin-left: 15px; background-color: #e74c3c; color: white; padding: 5px 10px; border-radius: 4px;">Remove</button>
                                        </form>
                                    </div>
                                </div>
                                <div style="margin-left: auto; font-weight: bold;">
                                    ₹<?php echo number_format($item['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="cart-summary-item">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="cart-summary-item">
                            <span>Shipping</span>
                            <span>₹30.00</span>
                        </div>
                        <div class="cart-total">
                            <span>Total</span>
                            <span>₹<?php echo number_format($total + 30, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn" style="display: block; text-align: center; margin-top: 20px;">Proceed to Checkout</a>
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
    <style>
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr !important;
    }
    
    .cart-item {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .cart-item img {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .cart-item-details {
        margin-bottom: 15px;
    }
    
    .cart-item-quantity form {
        justify-content: center;
    }
}
</style>
</body>
</html>

