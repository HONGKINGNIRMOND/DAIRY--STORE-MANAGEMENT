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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];
    $manager = $_POST['manager'];
    $contact_number = $_POST['contact_number'];
    
    // Insert warehouse
    $sql = "INSERT INTO warehouses (name, location, capacity, manager, contact_number) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $name, $location, $capacity, $manager, $contact_number);
    
    if ($stmt->execute()) {
        $success = "Warehouse added successfully!";
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
    <title>Add Warehouse - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Add New Warehouse</h2>
                <a href="warehouses.php" class="btn">Back to Warehouses</a>
            </div>
            
            <?php if($error != ''): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success != ''): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container" style="max-width: 800px; margin: 0;">
                <form action="add-warehouse.php" method="post">
                    <div class="form-group">
                        <label for="name">Warehouse Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <textarea id="location" name="location" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Capacity (in units)</label>
                        <input type="number" id="capacity" name="capacity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="manager">Manager Name</label>
                        <input type="text" id="manager" name="manager">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number">
                    </div>
                    
                    <button type="submit" class="form-btn">Add Warehouse</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

