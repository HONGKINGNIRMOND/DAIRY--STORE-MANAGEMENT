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
$error = '';
$success = '';

// Get user info
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Get cart items
$sql = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image, p.quantity_in_stock 
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

// Process order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipping_address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    
    // Check if cart is empty
    if (empty($cart_items)) {
        $error = "Your cart is empty";
    } else {
        // Check stock availability
        $stock_error = false;
        foreach ($cart_items as $item) {
            if ($item['quantity'] > $item['quantity_in_stock']) {
                $stock_error = true;
                $error = "Not enough stock for " . $item['name'];
                break;
            }
        }
        
        if (!$stock_error) {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Create order
                $order_sql = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_status) VALUES (?, ?, ?, ?)";
                $order_stmt = $conn->prepare($order_sql);
                $payment_status = ($payment_method == 'cod') ? 'pending' : 'paid';
                $total_with_shipping = $total + 30; // Adding ₹30 shipping
                $order_stmt->bind_param("idss", $user_id, $total_with_shipping, $shipping_address, $payment_status);
                $order_stmt->execute();
                $order_id = $conn->insert_id;
                $order_stmt->close();
                
                // Add order items and update stock
                foreach ($cart_items as $item) {
                    // Add to order items
                    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $item_stmt = $conn->prepare($item_sql);
                    $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                    $item_stmt->execute();
                    $item_stmt->close();
                    
                    // Update stock
                    $stock_sql = "UPDATE products SET quantity_in_stock = quantity_in_stock - ? WHERE id = ?";
                    $stock_stmt = $conn->prepare($stock_sql);
                    $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                    $stock_stmt->execute();
                    $stock_stmt->close();
                }
                
                // Clear cart
                $clear_sql = "DELETE FROM cart WHERE user_id = ?";
                $clear_stmt = $conn->prepare($clear_sql);
                $clear_stmt->bind_param("i", $user_id);
                $clear_stmt->execute();
                $clear_stmt->close();
                
                // Commit transaction
                $conn->commit();
                
                $success = "Order placed successfully! Your order ID is #" . $order_id;
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = "Error placing order: " . $e->getMessage();
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Fresh Dairy</title>
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
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="checkout-container">
        <div class="container">
            <h2>Checkout</h2>
            
            <?php if($error != ''): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success != ''): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; text-align: center;">
                    <h3><?php echo $success; ?></h3>
                    <p>Thank you for your purchase!</p>
                    <a href="index.php" class="btn" style="margin-top: 15px;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <?php if(empty($cart_items)): ?>
                    <div style="text-align: center; margin: 50px 0;">
                        <p>Your cart is empty.</p>
                        <a href="products.php" class="btn" style="margin-top: 20px;">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div class="checkout-form">
                            <h3>Shipping Information</h3>
                            <form action="checkout.php" method="post">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Shipping Address</label>
                                    <textarea id="address" name="address" required><?php echo $user['address']; ?></textarea>
                                </div>
                                
                                <h3>Payment Method</h3>
                                <div class="form-group">
                                    <label>
                                        <input type="radio" name="payment_method" value="cod" checked> Cash on Delivery
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="radio" name="payment_method" value="upi"> UPI Payment
                                    </label>
                                </div>
                                <div id="upi-details" style="display: none; margin-top: 10px;">
                                    <div class="form-group">
                                        <label for="upi_id">UPI ID</label>
                                        <input type="text" id="upi_id" name="upi_id" placeholder="example@upi">
                                        <small style="display: block; margin-top: 5px; color: #666;">Enter your UPI ID to make the payment</small>
                                    </div>
                                </div>

                                <script>
                                document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                                    radio.addEventListener('change', function() {
                                        const upiDetails = document.getElementById('upi-details');
                                        upiDetails.style.display = this.value === 'upi' ? 'block' : 'none';
                                        
                                        const upiInput = document.getElementById('upi_id');
                                        upiInput.required = this.value === 'upi';
                                    });
                                });
                                </script>
                                
                                <button type="submit" class="form-btn">Place Order</button>
                            </form>
                        </div>
                        
                        <div class="order-summary">
                            <h3>Order Summary</h3>
                            <div style="margin-bottom: 20px;">
                                <?php foreach($cart_items as $item): ?>
                                    <div style="display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                        <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; margin-right: 15px;">
                                        <div>
                                            <h4 style="margin: 0;"><?php echo $item['name']; ?></h4>
                                            <div style="color: #666; font-size: 14px;">Qty: <?php echo $item['quantity']; ?></div>
                                            <div style="font-weight: bold;">₹<?php echo number_format($item['subtotal'], 2); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="cart-summary">
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
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
    
    .checkout-form {
        order: 2;
    }
    
    .order-summary {
        order: 1;
        margin-bottom: 30px;
    }
}
</style>
</body>
</html>

