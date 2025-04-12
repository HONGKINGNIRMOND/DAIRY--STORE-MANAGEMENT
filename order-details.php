<?php
session_start();
require_once 'config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: profile.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    // Order not found or doesn't belong to user
    header("Location: profile.php");
    exit();
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// Get order items
$items_sql = "SELECT oi.*, p.name, p.image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = [];

if ($items_result->num_rows > 0) {
    while($row = $items_result->fetch_assoc()) {
        $order_items[] = $row;
    }
}
$items_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Fresh Dairy</title>
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
                    <li><a href="profile.php" class="active">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="order-details">
        <div class="container">
            <div style="margin-bottom: 20px;">
                <a href="profile.php#orders" style="display: inline-flex; align-items: center; color: #3498db;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Orders
                </a>
            </div>
            
            <h2>Order #<?php echo $order_id; ?></h2>
            
            <div class="order-info" style="background-color: white; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); padding: 20px; margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                    <div>
                        <h3>Order Information</h3>
                        <p><strong>Order Date:</strong> <?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></p>
                        <p>
                            <strong>Status:</strong> 
                            <span style="
                                padding: 3px 8px;
                                border-radius: 4px;
                                font-size: 12px;
                                background-color: <?php 
                                    echo $order['order_status'] == 'delivered' ? '#27ae60' : 
                                        ($order['order_status'] == 'shipped' ? '#3498db' : 
                                        ($order['order_status'] == 'processing' ? '#f39c12' : 
                                        ($order['order_status'] == 'cancelled' ? '#e74c3c' : '#95a5a6')));
                                ?>;
                                color: white;
                            ">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </p>
                        <p>
                            <strong>Payment Status:</strong> 
                            <span style="
                                padding: 3px 8px;
                                border-radius: 4px;
                                font-size: 12px;
                                background-color: <?php 
                                    echo $order['payment_status'] == 'paid' ? '#27ae60' : 
                                        ($order['payment_status'] == 'failed' ? '#e74c3c' : '#f39c12');
                                ?>;
                                color: white;
                            ">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <h3>Shipping Address</h3>
                        <p><?php echo nl2br($order['shipping_address']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="order-items" style="background-color: white; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); padding: 20px; margin: 20px 0;">
                <h3>Order Items</h3>
                
                <table class="data-table" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($order_items as $item): ?>
                            <tr>
                                <td style="display: flex; align-items: center;">
                                    <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                    <div><?php echo $item['name']; ?></div>
                                </td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Subtotal</strong></td>
                            <td>₹<?php echo number_format($order['total_amount'] - 30, 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Shipping</strong></td>
                            <td>₹30.00</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                            <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
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
    .order-info > div {
        flex-direction: column;
    }
    
    .order-info > div > div:first-child {
        margin-bottom: 20px;
    }
    
    .data-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .data-table td {
        min-width: 100px;
    }
    
    .data-table td:first-child {
        min-width: 200px;
    }
}
</style>
</body>
</html>

