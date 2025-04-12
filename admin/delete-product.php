<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Get product image before deletion
    $image_sql = "SELECT image FROM products WHERE id = ?";
    $image_stmt = $conn->prepare($image_sql);
    $image_stmt->bind_param("i", $product_id);
    $image_stmt->execute();
    $image_result = $image_stmt->get_result();
    
    if ($image_result->num_rows > 0) {
        $product = $image_result->fetch_assoc();
        // Delete product image if it's not the default image
        if ($product['image'] != 'default.jpg') {
            $image_path = "../images/products/" . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
    $image_stmt->close();
    
    // Delete product
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting product";
    }
    $stmt->close();
}

$conn->close();
header("Location: products.php");
exit();
?>

