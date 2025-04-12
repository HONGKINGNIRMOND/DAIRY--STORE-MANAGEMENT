<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

$error = '';
$success = '';

// Check if warehouse ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: warehouses.php");
    exit();
}

$warehouse_id = $_GET['id'];

// Get warehouse details
$warehouse_sql = "SELECT * FROM warehouses WHERE id = ?";
$warehouse_stmt = $conn->prepare($warehouse_sql);
$warehouse_stmt->bind_param("i", $warehouse_id);
$warehouse_stmt->execute();
$warehouse_result = $warehouse_stmt->get_result();

if ($warehouse_result->num_rows == 0) {
    header("Location: warehouses.php");
    exit();
}

$warehouse = $warehouse_result->fetch_assoc();
$warehouse_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];
    $manager = $_POST['manager'];
    $contact_number = $_POST['contact_number'];
    
    // Update warehouse
    $sql = "UPDATE warehouses SET name = ?, location = ?, capacity = ?, manager = ?, contact_number = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissi", $name, $location, $capacity, $manager, $contact_number, $warehouse_id);
    
    if ($stmt->execute()) {
        $success = "Warehouse updated successfully!";
        
        // Refresh warehouse data
        $warehouse_stmt = $conn->prepare($warehouse_sql);
        $warehouse_stmt->bind_param("i", $warehouse_id);
        $warehouse_stmt->execute();
        $warehouse_result = $warehouse_stmt->get_result();
        $warehouse = $warehouse_result->fetch_assoc();
        $warehouse_stmt->close();
    } else {
        $error = "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Warehouse - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Edit Warehouse</h2>
                <a href="warehouses.php" class="btn">Back to Warehouses</a>
            </div>
            
            <?php if($error != ''): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success != ''): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container" style="max-width: 800px; margin: 0;">
                <form action="edit-warehouse.php?id=<?php echo $warehouse_id; ?>" method="post">
                    <div class="form-group">
                        <label for="name">Warehouse Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $warehouse['name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <textarea id="location" name="location" required><?php echo $warehouse['location']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Capacity (in units)</label>
                        <input type="number" id="capacity" name="capacity" min="1" value="<?php echo $warehouse['capacity']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="manager">Manager Name</label>
                        <input type="text" id="manager" name="manager" value="<?php echo $warehouse['manager']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" value="<?php echo $warehouse['contact_number']; ?>">
                    </div>
                    
                    <button type="submit" class="form-btn">Update Warehouse</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

