-- Create database
CREATE DATABASE IF NOT EXISTS noodle_store;
USE noodle_store;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Noodle products table
CREATE TABLE noodles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_status ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    pickup_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    noodle_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (noodle_id) REFERENCES noodles(id) ON DELETE CASCADE
);

-- Payment transactions table (FIXED VERSION)
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    card_type VARCHAR(50),
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    response_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert sample noodles
INSERT INTO noodles (code, name, brand, price, stock, description) VALUES
('N001', 'Shin Ramyun', 'Nongshim', 5.99, 50, 'Spicy Korean instant noodles'),
('N002', 'Indomie Mi Goreng', 'Indomie', 3.99, 100, 'Indonesian fried noodles'),
('N003', 'Mama Tom Yum', 'Mama', 2.99, 75, 'Thai spicy shrimp flavor'),
('N004', 'Samyang Buldak', 'Samyang', 6.99, 30, 'Extra spicy Korean fire noodles'),
('N005', 'Maruchan Chicken', 'Maruchan', 1.99, 200, 'Classic chicken ramen'),
('N006', 'Cup Noodles', 'Nissin', 2.49, 150, 'Original cup noodles'),
('N007', 'Sapporo Ichiban', 'Sapporo', 2.29, 120, 'Japanese original flavor'),
('N008', 'Nissin Raoh', 'Nissin', 3.49, 60, 'Premium tonkotsu ramen');

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@noodlestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample regular user (password: user123)
INSERT INTO users (username, email, password, role) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');