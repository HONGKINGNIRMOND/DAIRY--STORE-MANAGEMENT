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

// Check if farmer ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: farmers.php");
    exit();
}

$farmer_id = $_GET['id'];

// Get farmer details
$farmer_sql = "SELECT * FROM farmers WHERE id = ?";
$farmer_stmt = $conn->prepare($farmer_sql);
$farmer_stmt->bind_param("i", $farmer_id);
$farmer_stmt->execute();
$farmer_result = $farmer_stmt->get_result();

if ($farmer_result->num_rows == 0) {
    header("Location: farmers.php");
    exit();
}

$farmer = $farmer_result->fetch_assoc();
$farmer_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact_person = $_POST['contact_person'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $products_supplied = $_POST['products_supplied'];
    
    // Update farmer
    $sql = "UPDATE farmers SET name = ?, contact_person = ?, email = ?, phone = ?, address = ?, products_supplied = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $name, $contact_person, $email, $phone, $address, $products_supplied, $farmer_id);
    
    if ($stmt->execute()) {
        $success = "Farmer updated successfully!";
        
        // Refresh farmer data
        $farmer_stmt = $conn->prepare($farmer_sql);
        $farmer_stmt->bind_param("i", $farmer_id);
        $farmer_stmt->execute();
        $farmer_result = $farmer_stmt->get_result();
        $farmer = $farmer_result->fetch_assoc();
        $farmer_stmt->close();
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
    <title>Edit Farmer - Fresh Dairy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h2>Edit Farmer</h2>
                <a href="farmers.php" class="btn">Back to Farmers</a>
            </div>
            
            <?php if($error != ''): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success != ''): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-container" style="max-width: 800px; margin: 0;">
                <form action="edit-farmer.php?id=<?php echo $farmer_id; ?>" method="post">
                    <div class="form-group">
                        <label for="name">Farm/Company Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $farmer['name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" id="contact_person" name="contact_person" value="<?php echo $farmer['contact_person']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $farmer['email']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo $farmer['phone']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required><?php echo $farmer['address']; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="products_supplied">Products Supplied</label>
                        <textarea id="products_supplied" name="products_supplied" placeholder="e.g. Milk, Cheese, Yogurt"><?php echo $farmer['products_supplied']; ?></textarea>
                    </div>
                    
                    <button type="submit" class="form-btn">Update Farmer</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

