<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $warehouse_id = $_GET['id'];
    
    // Check if warehouse has associated products
    $check_sql = "SELECT COUNT(*) as count FROM products WHERE warehouse_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $warehouse_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['error'] = "Cannot delete warehouse. Please reassign or delete associated products first.";
    } else {
        // Delete warehouse
        $sql = "DELETE FROM warehouses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $warehouse_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Warehouse deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting warehouse";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

$conn->close();
header("Location: warehouses.php");
exit();
?>

