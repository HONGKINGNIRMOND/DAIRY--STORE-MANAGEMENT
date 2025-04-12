<?php
session_start();
require_once 'config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    
    // Check if product exists and has enough stock
    $product_sql = "SELECT quantity_in_stock FROM products WHERE id = ?";
    $product_stmt = $conn->prepare($product_sql);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    
    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        
        if ($product['quantity_in_stock'] >= $quantity) {
            // Check if product already in cart
            $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update quantity
                $cart_item = $check_result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + $quantity;
                
                $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Add new cart item
                $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
            
            $check_stmt->close();
            
            // Redirect to cart
            header("Location: cart.php");
            exit();
        } else {
            // Not enough stock
            $_SESSION['error'] = "Sorry, not enough stock available.";
            header("Location: product-details.php?id=" . $product_id);
            exit();
        }
    } else {
        // Product not found
        $_SESSION['error'] = "Product not found.";
        header("Location: products.php");
        exit();
    }
    
    $product_stmt->close();
} else {
    // Invalid request
    header("Location: products.php");
    exit();
}

$conn->close();
?>

