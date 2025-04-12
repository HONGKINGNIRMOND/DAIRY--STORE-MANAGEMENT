<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Get all warehouses
$sql = "SELECT * FROM warehouses ORDER BY id DESC";
$result = $conn->query($sql);
$warehouses = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $warehouses[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Warehouses - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Manage Warehouses</h2>
                <a href="add-warehouse.php" class="btn">Add New Warehouse</a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Manager</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($warehouses as $warehouse): ?>
                        <tr>
                            <td>#<?php echo $warehouse['id']; ?></td>
                            <td><?php echo $warehouse['name']; ?></td>
                            <td><?php echo $warehouse['location']; ?></td>
                            <td><?php echo $warehouse['capacity']; ?></td>
                            <td><?php echo $warehouse['manager']; ?></td>
                            <td><?php echo $warehouse['contact_number']; ?></td>
                            <td>
                                <a href="edit-warehouse.php?id=<?php echo $warehouse['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <a href="delete-warehouse.php?id=<?php echo $warehouse['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this warehouse?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($warehouses)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No warehouses found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
    
    .data-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-header a {
        margin-top: 10px;
    }
}
</style>
</body>
</html>

