-- Existing installation migration for the innovation update.
-- Run this file once if database.sql was imported before the update.

ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS customization_json TEXT NULL AFTER price;

-- AI 修改：舊資料庫升級用，加上泡麵加料選項表與預設資料。
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
('chashu','炙燒叉燒片','Torched Chashu','無人加熱艙模擬炙燒風味。',1.45,42,'protein'),
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

-- AI 修改：活動、點數、兌換、合作店家與營業時間資料表由 includes/innovation.php
-- 首次開啟功能頁時自動建立；此註解保留給既有資料庫升級說明。
