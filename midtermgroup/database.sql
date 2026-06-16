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
    customization_json TEXT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (noodle_id) REFERENCES noodles(id) ON DELETE CASCADE
);

-- AI 修改：泡麵加料選項，dashboard、購物車與訂單 JSON 會共用這份價格。
CREATE TABLE topping_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) UNIQUE NOT NULL,
    name_zh VARCHAR(80) NOT NULL,
    name_en VARCHAR(80) NOT NULL,
    description VARCHAR(180) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category ENUM('size','protein','vegetable','sauce','limited') DEFAULT 'vegetable',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- AI 修改：一次性忘記密碼 Token，資料庫只保存雜湊值
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    requested_ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_reset_hash (token_hash),
    INDEX idx_password_reset_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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

INSERT INTO topping_options (code,name_zh,name_en,description,price,stock,category) VALUES
('large-noodle','加大麵量','Large Noodle Boost','多 35% 麵量，適合正餐份量。',0.79,80,'size'),
('ajitama','半熟溏心蛋','Ajitama Egg','自助機台冷藏艙取出的半熟蛋。',0.95,64,'protein'),
('cheese','融化起司片','Melted Cheese','讓辣味泡麵變得更濃郁。',0.70,72,'protein'),
('chashu','炙燒叉燒片','Torched Chashu','無人加熱艙模擬炙燒風味。',1.45,42,'protein'),
('corn','甜玉米粒','Sweet Corn','清甜口感，適合雞湯與味噌。',0.55,95,'vegetable'),
('seaweed','脆海苔片','Crispy Seaweed','取餐時獨立封裝避免軟化。',0.45,88,'vegetable'),
('scallion','青蔥增量','Extra Scallion','提升香氣與清爽感。',0.35,110,'vegetable'),
('spicy-oil','霓虹辣油','Neon Chili Oil','夜間限定辣油，帶微麻尾韻。',0.40,58,'sauce');

-- AI 修改：修正示範帳號密碼雜湊，讓 admin123 / user123 可實際登入
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@noodlestore.com', '$2y$10$IWefwwjp0dMQDKps4Mn52OEKJ4rkwyxdeNklz5P2Tb67GWzeC08Eu', 'admin');

INSERT INTO users (username, email, password, role) VALUES
('john_doe', 'john@example.com', '$2y$10$L13.to8A/.skjNVR/xiBM..Nol3neK6sef.RXWuDuwubEX5zwc2K2', 'user');

-- 若資料庫已建立，可單獨執行以下 UPDATE 修正示範帳號密碼：
-- UPDATE users SET password = '$2y$10$IWefwwjp0dMQDKps4Mn52OEKJ4rkwyxdeNklz5P2Tb67GWzeC08Eu' WHERE username = 'admin';
-- UPDATE users SET password = '$2y$10$L13.to8A/.skjNVR/xiBM..Nol3neK6sef.RXWuDuwubEX5zwc2K2' WHERE username = 'john_doe';
