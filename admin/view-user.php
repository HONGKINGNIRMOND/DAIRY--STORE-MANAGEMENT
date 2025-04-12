<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Get user details
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows == 0) {
    header("Location: users.php");
    exit();
}

$user = $user_result->fetch_assoc();
$user_stmt->close();

// Get user orders
$orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders = [];

if ($orders_result->num_rows > 0) {
    while($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$orders_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>User Details</h2>
                <a href="users.php" class="btn">Back to Users</a>
            </div>
            
            <div class="user-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="user-info" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>User Information</h3>
                    <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
                    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $user['phone'] ?? 'N/A'; ?></p>
                    <p><strong>Role:</strong> <?php echo $user['is_admin'] ? 'Admin' : 'Customer'; ?></p>
                    <p><strong>Joined Date:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                    
                    <h4 style="margin-top: 15px;">Address</h4>
                    <p><?php echo nl2br($user['address'] ?? 'No address provided'); ?></p>
                </div>
                
                <div class="user-stats" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>User Statistics</h3>
                    <p><strong>Total Orders:</strong> <?php echo count($orders); ?></p>
                    
                    <?php if(!empty($orders)): ?>
                        <?php 
                            $total_spent = 0;
                            foreach($orders as $order) {
                                $total_spent += $order['total_amount'];
                            }
                        ?>
                        <p><strong>Total Spent:</strong> ₹<?php echo number_format($total_spent, 2); ?></p>
                        <p><strong>Last Order:</strong> <?php echo date('F d, Y', strtotime($orders[0]['created_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="user-orders" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                <h3>Order History</h3>
                
                <?php if(empty($orders)): ?>
                    <p>This user has not placed any orders yet.</p>
                <?php else: ?>
                    <table class="data-table" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
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
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

