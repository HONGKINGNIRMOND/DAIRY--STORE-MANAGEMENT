<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Get all orders with user info
$sql = "SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
$orders = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Update order status if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $update_sql = "UPDATE orders SET order_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $status, $order_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Order status updated successfully";
        header("Location: orders.php");
        exit();
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
    <title>Manage Orders - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Manage Orders</h2>
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
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>
                                <?php echo $order['user_name']; ?><br>
                                <small><?php echo $order['user_email']; ?></small>
                            </td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <form action="orders.php" method="post" style="display: flex; gap: 10px;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="form-control" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                                        <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="action-btn edit-btn">Update</button>
                                </form>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <a href="view-order.php?id=<?php echo $order['id']; ?>" class="action-btn edit-btn">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
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
    
    .data-table td form {
        flex-direction: column;
        gap: 5px;
    }
    
    .data-table td form select {
        width: 100%;
    }
}
</style>
</html>

