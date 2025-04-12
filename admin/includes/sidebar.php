<div class="admin-sidebar">
    <h3>Admin Panel</h3>
    <ul>
        <li><a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
        <li><a href="products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'class="active"' : ''; ?>>Products</a></li>
        <li><a href="farmers.php" <?php echo basename($_SERVER['PHP_SELF']) == 'farmers.php' ? 'class="active"' : ''; ?>>Farmers</a></li>
        <li><a href="warehouses.php" <?php echo basename($_SERVER['PHP_SELF']) == 'warehouses.php' ? 'class="active"' : ''; ?>>Warehouses</a></li>
        <li><a href="orders.php" <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'class="active"' : ''; ?>>Orders</a></li>
        <li><a href="users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'class="active"' : ''; ?>>Users</a></li>
        <li><a href="../index.php">Back to Store</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>

