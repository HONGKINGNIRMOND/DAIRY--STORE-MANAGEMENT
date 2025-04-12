<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// Get order details
$order_sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    header("Location: orders.php");
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

// Update order status if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status'])) {
    $status = $_POST['status'];
    
    $update_sql = "UPDATE orders SET order_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $status, $order_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Order status updated successfully";
        
        // Refresh order data
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order = $order_result->fetch_assoc();
        $order_stmt->close();
    } else {
        $_SESSION['error'] = "Error updating order status";
    }
    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Order #<?php echo $order_id; ?> Details</h2>
                <a href="orders.php" class="btn">Back to Orders</a>
            </div>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px;">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="order-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="order-info" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
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
                    
                    <form action="view-order.php?id=<?php echo $order_id; ?>" method="post" style="margin-top: 20px;">
                        <div class="form-group">
                            <label for="status"><strong>Update Order Status:</strong></label>
                            <select name="status" id="status" class="form-control" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd; width: 100%;">
                                <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn" style="margin-top: 10px;">Update Status</button>
                    </form>
                </div>
                
                <div class="customer-info" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo $order['user_name']; ?></p>
                    <p><strong>Email:</strong> <?php echo $order['user_email']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $order['user_phone']; ?></p>
                    <h4 style="margin-top: 15px;">Shipping Address</h4>
                    <p><?php echo nl2br($order['shipping_address']); ?></p>
                </div>
            </div>
            
            <div class="order-items" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
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
                                    <img src="../images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
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
    </div>
<style>
@media (max-width: 768px) {
    .order-details {
        grid-template-columns: 1fr !important;
    }
    
    .order-info, .customer-info {
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
    
    .admin-container {
        grid-template-columns: 1fr !important;
    }
    
    .admin-sidebar {
        display: none;
    }
    
    .admin-content {
        padding: 10px;
    }
}
</style>
</body>
</html>

