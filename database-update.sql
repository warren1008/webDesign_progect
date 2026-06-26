ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS customization_json TEXT NULL AFTER price;
ALTER TABLE noodles
    ADD COLUMN IF NOT EXISTS image VARCHAR(255) NULL AFTER stock;
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL AFTER email;
UPDATE noodles SET image = 'assets/images/noodles/N001-shin-ramyun.webp' WHERE code = 'N001';
UPDATE noodles SET image = 'assets/images/noodles/N002-mi-goreng.webp' WHERE code = 'N002';
UPDATE noodles SET image = 'assets/images/noodles/N003-tom-yum.webp' WHERE code = 'N003';
UPDATE noodles SET image = 'assets/images/noodles/N004-buldak.webp' WHERE code = 'N004';
UPDATE noodles SET image = 'assets/images/noodles/N005-chicken.webp' WHERE code = 'N005';
UPDATE noodles SET image = 'assets/images/noodles/N006-cup.webp' WHERE code = 'N006';
UPDATE noodles SET image = 'assets/images/noodles/N007-shoyu.webp' WHERE code = 'N007';
UPDATE noodles SET image = 'assets/images/noodles/N008-tonkotsu.webp' WHERE code = 'N008';
CREATE TABLE IF NOT EXISTS rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    name_zh VARCHAR(120) NOT NULL,
    description VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NULL,
    points_required INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    reward_type ENUM('topping','coupon','limited') DEFAULT 'coupon',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE rewards
    ADD COLUMN IF NOT EXISTS image_path VARCHAR(255) NULL AFTER description;
UPDATE rewards SET image_path = 'assets/images/rewards/reward-ajitama-egg.png' WHERE id = 1 OR name = 'Free Ajitama Egg';
UPDATE rewards SET image_path = 'assets/images/rewards/reward-partner-tea.png' WHERE id = 2 OR name = 'Partner Tea Voucher';
UPDATE rewards SET image_path = 'assets/images/rewards/reward-neon-bowl.png' WHERE id = 3 OR name = 'Limited Neon Ramen Bowl';
CREATE TABLE IF NOT EXISTS topping_options (
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
INSERT IGNORE INTO topping_options (code,name_zh,name_en,description,price,stock,category) VALUES
('large-noodle','加大麵量','Large Noodle Boost','多 35% 麵量，適合正餐份量。',0.79,80,'size'),
('ajitama','半熟溏心蛋','Ajitama Egg','自助機台冷藏艙取出的半熟蛋。',0.95,64,'protein'),
('cheese','融化起司片','Melted Cheese','讓辣味泡麵變得更濃郁。',0.70,72,'protein'),
('chashu','炙燒叉燒片','Torched Chashu','無人加熱艙呈現炙燒風味。',1.45,42,'protein'),
('corn','甜玉米粒','Sweet Corn','清甜口感，適合雞湯與味噌。',0.55,95,'vegetable'),
('seaweed','脆海苔片','Crispy Seaweed','取餐時獨立封裝避免軟化。',0.45,88,'vegetable'),
('scallion','青蔥增量','Extra Scallion','提升香氣與清爽感。',0.35,110,'vegetable'),
('spicy-oil','霓虹辣油','Neon Chili Oil','夜間限定辣油，帶微麻尾韻。',0.40,58,'sauce');
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    requested_ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_reset_hash (token_hash),
    INDEX idx_password_reset_user (user_id),
    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS feedback_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(120) NULL,
    category VARCHAR(40) NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    message TEXT NOT NULL,
    status ENUM('new','reviewing','resolved') DEFAULT 'new',
    admin_note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
