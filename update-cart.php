<?php
session_start();
require_once 'config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cart_id']) && isset($_POST['action'])) {
    $cart_id = $_POST['cart_id'];
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];
    
    // Verify the cart item belongs to the user
    $check_sql = "SELECT * FROM cart WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $cart_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $cart_item = $check_result->fetch_assoc();
        
        if ($action == "increase") {
            // Increase quantity
            $sql = "UPDATE cart SET quantity = quantity + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $stmt->close();
        } else if ($action == "decrease") {
            // Decrease quantity, but don't go below 1
            if ($cart_item['quantity'] > 1) {
                $sql = "UPDATE cart SET quantity = quantity - 1 WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $cart_id);
                $stmt->execute();
                $stmt->close();
            } else {
                // If quantity would be 0, remove the item
                $sql = "DELETE FROM cart WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $cart_id);
                $stmt->execute();
                $stmt->close();
            }
        } else if ($action == "remove") {
            // Remove item from cart
            $sql = "DELETE FROM cart WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    $check_stmt->close();
}

// Redirect back to cart
header("Location: cart.php");
exit();
?>

