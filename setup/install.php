<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS dairy_store";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("dairy_store");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create farmers table
$sql = "CREATE TABLE IF NOT EXISTS farmers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    products_supplied TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Farmers table created successfully<br>";
} else {
    echo "Error creating farmers table: " . $conn->error . "<br>";
}

// Create warehouses table
$sql = "CREATE TABLE IF NOT EXISTS warehouses (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location TEXT NOT NULL,
    capacity INT(11) NOT NULL,
    manager VARCHAR(100),
    contact_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Warehouses table created successfully<br>";
} else {
    echo "Error creating warehouses table: " . $conn->error . "<br>";
}

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    category VARCHAR(50),
    farmer_id INT(11),
    warehouse_id INT(11),
    quantity_in_stock INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Products table created successfully<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Orders table created successfully<br>";
} else {
    echo "Error creating orders table: " . $conn->error . "<br>";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Order items table created successfully<br>";
} else {
    echo "Error creating order items table: " . $conn->error . "<br>";
}

// Create cart table
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, product_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Cart table created successfully<br>";
} else {
    echo "Error creating cart table: " . $conn->error . "<br>";
}

// Insert admin user
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, password, is_admin) 
        VALUES ('Admin User', 'admin@example.com', '$admin_password', 1)";

if ($conn->query($sql) === TRUE) {
    echo "Admin user created successfully<br>";
} else {
    echo "Error creating admin user: " . $conn->error . "<br>";
}

// Insert sample data
// Sample farmers
$sql = "INSERT INTO farmers (name, contact_person, email, phone, address, products_supplied) VALUES
('Green Meadows Farm', 'John Smith', 'john@greenmeadows.com', '555-1234', '123 Rural Road, Countryside', 'Milk, Cheese, Yogurt'),
('Sunny Valley Dairy', 'Mary Johnson', 'mary@sunnyvalley.com', '555-5678', '456 Valley Lane, Farmtown', 'Milk, Butter, Cream'),
('Highland Cattle Co.', 'Robert Brown', 'robert@highland.com', '555-9012', '789 Mountain Path, Upland', 'Milk, Ice Cream')";

if ($conn->query($sql) === TRUE) {
    echo "Sample farmers created successfully<br>";
} else {
    echo "Error creating sample farmers: " . $conn->error . "<br>";
}

// Sample warehouses
$sql = "INSERT INTO warehouses (name, location, capacity, manager, contact_number) VALUES
('Central Storage', '100 Main Street, Downtown', 5000, 'David Wilson', '555-3456'),
('North Distribution Center', '200 North Avenue, Northside', 3000, 'Sarah Lee', '555-7890'),
('East Facility', '300 East Boulevard, Easttown', 4000, 'Michael Chen', '555-1234')";

if ($conn->query($sql) === TRUE) {
    echo "Sample warehouses created successfully<br>";
} else {
    echo "Error creating sample warehouses: " . $conn->error . "<br>";
}

// Sample products
$sql = "INSERT INTO products (name, description, price, category, farmer_id, warehouse_id, quantity_in_stock) VALUES
('Fresh Whole Milk', 'Farm-fresh whole milk, pasteurized and homogenized', 3.99, 'Milk', 1, 1, 100),
('Organic Yogurt', 'Creamy organic yogurt with live cultures', 4.99, 'Yogurt', 1, 1, 75),
('Artisan Cheese', 'Hand-crafted artisan cheese aged to perfection', 6.99, 'Cheese', 2, 2, 50),
('Salted Butter', 'Rich and creamy salted butter', 3.49, 'Butter', 2, 2, 80),
('Chocolate Milk', 'Delicious chocolate-flavored milk', 4.29, 'Milk', 3, 3, 60),
('Heavy Cream', 'Fresh heavy cream for cooking and whipping', 5.99, 'Cream', 3, 3, 40)";

if ($conn->query($sql) === TRUE) {
    echo "Sample products created successfully<br>";
} else {
    echo "Error creating sample products: " . $conn->error . "<br>";
}

echo "<br>Setup completed successfully! <a href='../index.php'>Go to homepage</a>";

$conn->close();
?>

