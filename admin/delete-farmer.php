<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $farmer_id = $_GET['id'];
    
    // Check if farmer has associated products
    $check_sql = "SELECT COUNT(*) as count FROM products WHERE farmer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $farmer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $_SESSION['error'] = "Cannot delete farmer. Please reassign or delete associated products first.";
    } else {
        // Delete farmer
        $sql = "DELETE FROM farmers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $farmer_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Farmer deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting farmer";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

$conn->close();
header("Location: farmers.php");
exit();
?>

