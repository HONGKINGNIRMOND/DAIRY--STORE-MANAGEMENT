<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Get counts for dashboard
$counts = [
    'products' => 0,
    'farmers' => 0,
    'warehouses' => 0,
    'orders' => 0,
    'users' => 0
];

$tables = ['products', 'farmers', 'warehouses', 'orders', 'users'];
foreach ($tables as $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $counts[$table] = $row['count'];
    }
}

// Get recent orders
$recent_orders = [];
$sql = "SELECT o.id, o.total_amount, o.order_status, o.created_at, u.name as user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

// Get low stock products
$low_stock = [];
$sql = "SELECT id, name, quantity_in_stock FROM products WHERE quantity_in_stock < 10 ORDER BY quantity_in_stock ASC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $low_stock[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Dashboard</h2>
                <div>
                    <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                </div>
            </div>
            
            <div class="dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="stat-card" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>Products</h3>
                    <p style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $counts['products']; ?></p>
                </div>
                <div class="stat-card" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>Farmers</h3>
                    <p style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $counts['farmers']; ?></p>
                </div>
                <div class="stat-card" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>Warehouses</h3>
                    <p style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $counts['warehouses']; ?></p>
                </div>
                <div class="stat-card" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>Orders</h3>
                    <p style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $counts['orders']; ?></p>
                </div>
                <div class="stat-card" style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);">
                    <h3>Users</h3>
                    <p style="font-size: 24px; font-weight: bold; margin-top: 10px;"><?php echo $counts['users']; ?></p>
                </div>
            </div>
            
            <div class="dashboard-sections" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div class="dashboard-section">
                    <h3>Recent Orders</h3>
                    <?php if(empty($recent_orders)): ?>
                        <p>No orders yet.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo $order['user_name']; ?></td>
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
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <h3>Low Stock Products</h3>
                    <?php if(empty($low_stock)): ?>
                        <p>No low stock products.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Name</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($low_stock as $product): ?>
                                    <tr>
                                        <td>#<?php echo $product['id']; ?></td>
                                        <td><?php echo $product['name']; ?></td>
                                        <td>
                                            <span style="
                                                color: <?php echo $product['quantity_in_stock'] <= 5 ? '#e74c3c' : '#f39c12'; ?>;
                                                font-weight: bold;
                                            ">
                                                <?php echo $product['quantity_in_stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn">Update Stock</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
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
    
    .dashboard-stats {
        grid-template-columns: 1fr 1fr !important;
    }
    
    .dashboard-sections {
        grid-template-columns: 1fr !important;
    }
    
    .data-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>
</body>
</html>

