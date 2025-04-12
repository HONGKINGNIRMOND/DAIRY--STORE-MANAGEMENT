
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Dairy - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>Fresh Dairy</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h2>Fresh Dairy Products Delivered to Your Doorstep</h2>
            <p>Directly from local farmers to your table</p>
            <a href="products.php" class="btn">Shop Now</a>
        </div>
    </section>

    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php
                require_once 'config/db.php';
                
                $sql = "SELECT * FROM products ORDER BY id DESC LIMIT 4";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="product-card">';
                        echo '<img src="images/products/' . $row["image"] . '" alt="' . $row["name"] . '">';
                        echo '<h3>' . $row["name"] . '</h3>';
                        echo '<p class="price">₹' . number_format($row["price"], 2) . '</p>';
                        echo '<p>' . substr($row["description"], 0, 100) . '...</p>';
                        echo '<a href="product-details.php?id=' . $row["id"] . '" class="btn">View Details</a>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No products available</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <section class="about">
        <div class="container">
            <h2>About Fresh Dairy</h2>
            <p>We connect local dairy farmers directly with consumers, ensuring you get the freshest dairy products while supporting local agriculture.</p>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 Fresh Dairy. All rights reserved.</p>
        </div>
    </footer>

<style>
@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(100%, 1fr)) !important;
    }
    
    .product-card {
        max-width: 100%;
    }
    
    header .container {
        flex-direction: column;
    }
    
    nav ul {
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 15px;
    }
    
    nav ul li {
        margin: 5px;
    }
    
    .hero {
        padding: 40px 0;
    }
    
    .hero h2 {
        font-size: 28px;
    }
}
</style>
</body>
</html>

