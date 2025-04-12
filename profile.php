<?php
session_start();
require_once 'config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
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

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!empty($current_password)) {
        if (password_verify($current_password, $user['password'])) {
            // Check if new password is provided and matches confirmation
            if (!empty($new_password)) {
                if ($new_password === $confirm_password) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("sssssi", $name, $email, $phone, $address, $hashed_password, $user_id);
                } else {
                    $error = "New passwords do not match";
                }
            } else {
                // Update without changing password
                $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
            }
            
            if (empty($error)) {
                if ($update_stmt->execute()) {
                    $success = "Profile updated successfully";
                    
                    // Update session variables
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    // Refresh user data
                    $user_stmt = $conn->prepare($user_sql);
                    $user_stmt->bind_param("i", $user_id);
                    $user_stmt->execute();
                    $user_result = $user_stmt->get_result();
                    $user = $user_result->fetch_assoc();
                    $user_stmt->close();
                } else {
                    $error = "Error updating profile: " . $conn->error;
                }
                
                $update_stmt->close();
            }
        } else {
            $error = "Current password is incorrect";
        }
    } else {
        // Update without password verification (not changing password)
        $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully";
            
            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Refresh user data
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user = $user_result->fetch_assoc();
            $user_stmt->close();
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        
        $update_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Fresh Dairy</title>
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

    <section class="profile-section">
        <div class="container">
            <h2>My Profile</h2>
            
            <?php if($error != ''): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px; background-color: #ffeeee; padding: 10px; border-radius: 4px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success != ''): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px; background-color: #eeffee; padding: 10px; border-radius: 4px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin: 30px 0;">
                <div class="profile-sidebar">
                    <div class="profile-menu" style="background-color: white; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); padding: 20px;">
                        <h3>Account Menu</h3>
                        <ul style="list-style: none; padding: 0; margin: 15px 0;">
                            <li style="margin-bottom: 10px;"><a href="#profile" style="display: block; padding: 10px; background-color: #f2f2f2; border-radius: 4px; color: #333; text-decoration: none;">Profile Information</a></li>
                            <li style="margin-bottom: 10px;"><a href="#orders" style="display: block; padding: 10px; background-color: #f2f2f2; border-radius: 4px; color: #333; text-decoration: none;">Order History</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div id="profile" class="profile-section" style="background-color: white; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); padding: 20px; margin-bottom: 30px;">
                        <h3>Profile Information</h3>
                        <form action="profile.php" method="post" style="margin-top: 20px;">
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
                                <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address"><?php echo $user['address']; ?></textarea>
                            </div>
                            
                            <h4 style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">Change Password</h4>
                            <p style="color: #666; margin-bottom: 15px; font-size: 14px;">Leave blank if you don't want to change your password</p>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <button type="submit" name="update_profile" class="form-btn">Update Profile</button>
                        </form>
                    </div>
                    
                    <div id="orders" class="orders-section" style="background-color: white; border-radius: 8px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); padding: 20px;">
                        <h3>Order History</h3>
                        
                        <?php if(empty($orders)): ?>
                            <p style="margin-top: 15px;">You haven't placed any orders yet.</p>
                            <a href="products.php" class="btn" style="display: inline-block; margin-top: 15px;">Shop Now</a>
                        <?php else: ?>
                            <table class="data-table" style="margin-top: 15px;">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
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
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="action-btn edit-btn">View Details</a>
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
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 Fresh Dairy. All rights reserved.</p>
        </div>
    </footer>
<style>
@media (max-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr !important;
    }
    
    .profile-sidebar {
        margin-bottom: 20px;
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

