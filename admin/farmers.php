<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Get all farmers
$sql = "SELECT * FROM farmers ORDER BY id DESC";
$result = $conn->query($sql);
$farmers = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $farmers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Farmers - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Manage Farmers</h2>
                <a href="add-farmer.php" class="btn">Add New Farmer</a>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Products Supplied</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($farmers as $farmer): ?>
                        <tr>
                            <td>#<?php echo $farmer['id']; ?></td>
                            <td><?php echo $farmer['name']; ?></td>
                            <td><?php echo $farmer['contact_person']; ?></td>
                            <td><?php echo $farmer['email']; ?></td>
                            <td><?php echo $farmer['phone']; ?></td>
                            <td><?php echo $farmer['products_supplied']; ?></td>
                            <td>
                                <a href="edit-farmer.php?id=<?php echo $farmer['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <a href="delete-farmer.php?id=<?php echo $farmer['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this farmer?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($farmers)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No farmers found</td>
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

