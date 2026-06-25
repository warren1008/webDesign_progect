<?php
// AI 修改：促銷、會員點數、兌換獎品、合作店家與營業時間共用邏輯。

const POINTS_PER_TWD = 0.1;
const POINTS_REDEEM_TWD = 0.1;
const DEFAULT_USD_TWD_RATE = 31.606;

function innovationTableExists($table) {
    global $conn, $db_error;
    if (!empty($db_error)) return false;
    $safe = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    $result = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $result && $result->num_rows > 0;
}

function ensureInnovationSchema() {
    global $conn, $db_error;
    static $ready = false;
    if ($ready || !empty($db_error)) return empty($db_error);

    $queries = [
        "CREATE TABLE IF NOT EXISTS promotions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(120) NOT NULL,
            title_zh VARCHAR(120) NOT NULL,
            description VARCHAR(255) NOT NULL,
            promotion_type ENUM('threshold','product_quantity','percentage') NOT NULL,
            product_code VARCHAR(20) NULL,
            threshold_amount DECIMAL(10,2) DEFAULT 0,
            required_quantity INT DEFAULT 0,
            discount_value DECIMAL(10,2) NOT NULL,
            starts_at DATETIME NOT NULL,
            ends_at DATETIME NOT NULL,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS user_points (
            user_id INT PRIMARY KEY,
            balance INT NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS point_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            points INT NOT NULL,
            transaction_type ENUM('earn','redeem','adjust') NOT NULL,
            description VARCHAR(255) NOT NULL,
            order_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS rewards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            name_zh VARCHAR(120) NOT NULL,
            description VARCHAR(255) NOT NULL,
            points_required INT NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            reward_type ENUM('topping','coupon','limited') DEFAULT 'coupon',
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS reward_redemptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            reward_id INT NOT NULL,
            points_spent INT NOT NULL,
            redemption_code VARCHAR(20) UNIQUE NOT NULL,
            status ENUM('available','used','expired') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS partners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            category VARCHAR(80) NOT NULL,
            description VARCHAR(255) NOT NULL,
            offer_text VARCHAR(180) NOT NULL,
            open_time TIME NOT NULL,
            close_time TIME NOT NULL,
            color VARCHAR(20) DEFAULT '#00ffff',
            active TINYINT(1) DEFAULT 1
        )",
        "CREATE TABLE IF NOT EXISTS store_hours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_name VARCHAR(100) NOT NULL,
            service_name_zh VARCHAR(100) NOT NULL,
            day_of_week TINYINT NOT NULL,
            open_time TIME NULL,
            close_time TIME NULL,
            is_24_hours TINYINT(1) DEFAULT 0,
            is_closed TINYINT(1) DEFAULT 0,
            UNIQUE KEY service_day (service_name, day_of_week)
        )",
        "CREATE TABLE IF NOT EXISTS order_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL,
            comment VARCHAR(255) NULL,
            points_awarded INT NOT NULL DEFAULT 10,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY one_review_per_order (order_id, user_id),
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS support_cases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            order_number VARCHAR(50) NULL,
            issue_type VARCHAR(50) NOT NULL,
            status ENUM('demo_open','demo_closed') DEFAULT 'demo_open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS topping_options (
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
        )",
    ];

    foreach ($queries as $query) {
        if (!$conn->query($query)) return false;
    }

    $orderColumns = [
        'subtotal_amount' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
        'discount_amount' => "DECIMAL(10,2) NOT NULL DEFAULT 0",
        'points_used' => "INT NOT NULL DEFAULT 0",
        'points_earned' => "INT NOT NULL DEFAULT 0",
        'promotion_summary' => "VARCHAR(255) NULL",
    ];
    foreach ($orderColumns as $column => $definition) {
        $result = $conn->query("SHOW COLUMNS FROM orders LIKE '{$column}'");
        if ($result && $result->num_rows === 0) {
            $conn->query("ALTER TABLE orders ADD COLUMN {$column} {$definition}");
        }
    }

    $conn->query("INSERT IGNORE INTO user_points (user_id, balance)
        SELECT id, IF(username = 'john_doe', 480, 0) FROM users");

    if ((int)$conn->query("SELECT COUNT(*) AS count FROM promotions")->fetch_assoc()['count'] === 0) {
        $conn->query("INSERT INTO promotions
            (title,title_zh,description,promotion_type,product_code,threshold_amount,required_quantity,discount_value,starts_at,ends_at)
            VALUES
            ('Spend NT$300 Save NT$30','滿 NT$300 折 NT$30','結帳金額達 NT$300 自動折抵 NT$30','threshold',NULL,9.49,0,0.95,NOW(),DATE_ADD(NOW(),INTERVAL 30 DAY)),
            ('Shin Ramyun Duo','辛拉麵雙入優惠','購買兩份 N001 自動折抵 NT$25','product_quantity','N001',0,2,0.79,NOW(),DATE_ADD(NOW(),INTERVAL 21 DAY)),
            ('Fire Night 10% Off','火辣夜限定 9 折','N004 火辣雞麵期間限定九折','percentage','N004',0,1,10,NOW(),DATE_ADD(NOW(),INTERVAL 14 DAY))");
    }

    if ((int)$conn->query("SELECT COUNT(*) AS count FROM rewards")->fetch_assoc()['count'] === 0) {
        $conn->query("INSERT INTO rewards
            (name,name_zh,description,points_required,stock,reward_type) VALUES
            ('Free Ajitama Egg','免費溏心蛋','兌換後可於下一碗客製拉麵免費加蛋',80,50,'topping'),
            ('Partner Tea Voucher','合作店家茶飲券','可至 NEON TEA 兌換限定無糖冷泡茶',150,25,'coupon'),
            ('Limited Neon Ramen Bowl','限量霓虹拉麵碗','期末展示限定收藏碗，限量 12 份',300,12,'limited')");
    }

    if ((int)$conn->query("SELECT COUNT(*) AS count FROM partners")->fetch_assoc()['count'] === 0) {
        $conn->query("INSERT INTO partners
            (name,category,description,offer_text,open_time,close_time,color) VALUES
            ('NEON TEA','Tea Lab','主打無糖冷泡茶與氣泡飲的模擬合作品牌','拉麵訂單滿 NT$250，茶飲折 NT$20','10:30:00','22:00:00','#00ffff'),
            ('BYTE MART','Smart Convenience','提供飯糰、甜點與日用品的智慧便利店','會員出示取餐碼可獲限定甜點 9 折','07:00:00','23:30:00','#00ff88'),
            ('MOCHI LAB','Dessert Studio','每日少量製作科技感麻糬甜點','兌換 150 點可獲聯名麻糬一份','12:00:00','21:00:00','#ff00ff')");
    }

    // AI 修改：新增泡麵加料艙資料，讓 dashboard、購物車與訂單共用後端價格。
    if ((int)$conn->query("SELECT COUNT(*) AS count FROM topping_options")->fetch_assoc()['count'] === 0) {
        $conn->query("INSERT INTO topping_options
            (code,name_zh,name_en,description,price,stock,category) VALUES
            ('large-noodle','加大麵量','Large Noodle Boost','多 35% 麵量，適合正餐份量。',0.79,80,'size'),
            ('ajitama','半熟溏心蛋','Ajitama Egg','自助機台冷藏艙取出的半熟蛋。',0.95,64,'protein'),
            ('cheese','融化起司片','Melted Cheese','讓辣味泡麵變得更濃郁。',0.70,72,'protein'),
            ('chashu','炙燒叉燒片','Torched Chashu','無人加熱艙模擬炙燒風味。',1.45,42,'protein'),
            ('corn','甜玉米粒','Sweet Corn','清甜口感，適合雞湯與味噌。',0.55,95,'vegetable'),
            ('seaweed','脆海苔片','Crispy Seaweed','取餐時獨立封裝避免軟化。',0.45,88,'vegetable'),
            ('scallion','青蔥增量','Extra Scallion','提升香氣與清爽感。',0.35,110,'vegetable'),
            ('spicy-oil','霓虹辣油','Neon Chili Oil','夜間限定辣油，帶微麻尾韻。',0.40,58,'sauce')");
    }

    if ((int)$conn->query("SELECT COUNT(*) AS count FROM store_hours")->fetch_assoc()['count'] === 0) {
        for ($day = 0; $day <= 6; $day++) {
            $stmt = $conn->prepare("INSERT INTO store_hours
                (service_name,service_name_zh,day_of_week,open_time,close_time,is_24_hours)
                VALUES ('Staffless Store','無人商店',?,NULL,NULL,1),
                ('Automated Kitchen','自動化廚房',?,'10:00:00','22:30:00',0),
                ('Pickup Locker','無人取餐櫃',?,'06:00:00','23:59:00',0)");
            $stmt->bind_param('iii', $day, $day, $day);
            $stmt->execute();
        }
    }

    $ready = true;
    return true;
}

function getUsdTwdRate() {
    $cache = __DIR__ . '/../storage/cache/usd-twd.json';
    if (is_file($cache)) {
        $data = json_decode((string)file_get_contents($cache), true);
        if (is_array($data) && (float)($data['rate'] ?? 0) > 0) return (float)$data['rate'];
    }
    return DEFAULT_USD_TWD_RATE;
}

function getActivePromotions() {
    global $conn, $db_error;
    if (!ensureInnovationSchema() || !empty($db_error)) return [];
    $result = $conn->query("SELECT * FROM promotions
        WHERE active = 1 AND NOW() BETWEEN starts_at AND ends_at ORDER BY ends_at");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function calculateCartBenefits($cart, $pointsToUse = 0, $userId = null) {
    $subtotal = 0.0;
    $quantities = [];
    $itemTotals = [];
    foreach ($cart as $item) {
        $line = (float)$item['price'] * (int)$item['quantity'];
        $subtotal += $line;
        $code = (string)($item['code'] ?? '');
        $quantities[$code] = ($quantities[$code] ?? 0) + (int)$item['quantity'];
        $itemTotals[$code] = ($itemTotals[$code] ?? 0) + $line;
    }

    $discount = 0.0;
    $applied = [];
    foreach (getActivePromotions() as $promotion) {
        $value = 0.0;
        if ($promotion['promotion_type'] === 'threshold' && $subtotal >= (float)$promotion['threshold_amount']) {
            $value = (float)$promotion['discount_value'];
        } elseif ($promotion['promotion_type'] === 'product_quantity'
            && ($quantities[$promotion['product_code']] ?? 0) >= (int)$promotion['required_quantity']) {
            $value = (float)$promotion['discount_value'];
        } elseif ($promotion['promotion_type'] === 'percentage'
            && ($quantities[$promotion['product_code']] ?? 0) >= max(1, (int)$promotion['required_quantity'])) {
            $value = ($itemTotals[$promotion['product_code']] ?? 0) * ((float)$promotion['discount_value'] / 100);
        }
        if ($value > 0) {
            $discount += $value;
            $applied[] = $promotion;
        }
    }

    $afterPromotion = max(0, $subtotal - $discount);
    $balance = $userId ? getUserPointBalance($userId) : 0;
    $requested = max(0, min((int)$pointsToUse, $balance));
    $maxRedeemTwd = $afterPromotion * getUsdTwdRate() * 0.5;
    $maxPoints = (int)floor($maxRedeemTwd / POINTS_REDEEM_TWD);
    $pointsUsed = min($requested, $maxPoints);
    $pointDiscount = ($pointsUsed * POINTS_REDEEM_TWD) / getUsdTwdRate();
    $final = max(0, $afterPromotion - $pointDiscount);
    $pointsEarned = (int)floor(($final * getUsdTwdRate()) * POINTS_PER_TWD);

    return [
        'subtotal' => round($subtotal, 2),
        'promotion_discount' => round($discount, 2),
        'point_discount' => round($pointDiscount, 2),
        'discount' => round($discount + $pointDiscount, 2),
        'final_total' => round($final, 2),
        'points_used' => $pointsUsed,
        'points_earned' => $pointsEarned,
        'point_balance' => $balance,
        'applied_promotions' => $applied,
    ];
}

function getUserPointBalance($userId) {
    global $conn;
    if (!ensureInnovationSchema()) return 0;
    $stmt = $conn->prepare("SELECT balance FROM user_points WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return (int)($stmt->get_result()->fetch_assoc()['balance'] ?? 0);
}

function changeUserPoints($userId, $points, $type, $description, $orderId = null) {
    global $conn;
    if (!ensureInnovationSchema() || $points === 0) return false;
    $conn->query("INSERT IGNORE INTO user_points (user_id,balance) VALUES (" . (int)$userId . ",0)");
    $stmt = $conn->prepare("UPDATE user_points SET balance = GREATEST(0,balance + ?) WHERE user_id = ?");
    $stmt->bind_param('ii', $points, $userId);
    if (!$stmt->execute()) return false;
    $stmt = $conn->prepare("INSERT INTO point_transactions
        (user_id,points,transaction_type,description,order_id) VALUES (?,?,?,?,?)");
    $stmt->bind_param('iissi', $userId, $points, $type, $description, $orderId);
    return $stmt->execute();
}

function getRewards() {
    global $conn;
    if (!ensureInnovationSchema()) return [];
    $result = $conn->query("SELECT * FROM rewards WHERE active = 1 ORDER BY points_required");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function redeemReward($userId, $rewardId) {
    global $conn;
    if (!ensureInnovationSchema()) return ['success' => false, 'message' => 'Reward system unavailable.'];
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT * FROM rewards WHERE id = ? AND active = 1 FOR UPDATE");
        $stmt->bind_param('i', $rewardId);
        $stmt->execute();
        $reward = $stmt->get_result()->fetch_assoc();
        $balance = getUserPointBalance($userId);
        if (!$reward || (int)$reward['stock'] <= 0) throw new Exception('This reward is sold out.');
        if ($balance < (int)$reward['points_required']) throw new Exception('Not enough points.');
        $points = -(int)$reward['points_required'];
        $code = 'RWD-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        $stmt = $conn->prepare("UPDATE user_points SET balance = balance + ? WHERE user_id = ?");
        $stmt->bind_param('ii', $points, $userId);
        $stmt->execute();
        $stmt = $conn->prepare("UPDATE rewards SET stock = stock - 1 WHERE id = ?");
        $stmt->bind_param('i', $rewardId);
        $stmt->execute();
        $stmt = $conn->prepare("INSERT INTO reward_redemptions
            (user_id,reward_id,points_spent,redemption_code) VALUES (?,?,?,?)");
        $spent = abs($points);
        $stmt->bind_param('iiis', $userId, $rewardId, $spent, $code);
        $stmt->execute();
        $stmt = $conn->prepare("INSERT INTO point_transactions
            (user_id,points,transaction_type,description) VALUES (?,?,'redeem',?)");
        $description = 'Redeemed ' . $reward['name'];
        $stmt->bind_param('iis', $userId, $points, $description);
        $stmt->execute();
        $conn->commit();
        return ['success' => true, 'message' => 'Reward redeemed.', 'code' => $code];
    } catch (Throwable $error) {
        $conn->rollback();
        return ['success' => false, 'message' => $error->getMessage()];
    }
}

function getPartners() {
    global $conn;
    if (!ensureInnovationSchema()) return [];
    $result = $conn->query("SELECT * FROM partners WHERE active = 1 ORDER BY id");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function serviceStatus($openTime, $closeTime, $is24Hours = false, $isClosed = false) {
    if ($isClosed) return ['key' => 'closed', 'label' => '休息中'];
    if ($is24Hours) return ['key' => 'open', 'label' => '24 小時營業'];
    $now = new DateTimeImmutable();
    $open = new DateTimeImmutable(date('Y-m-d') . ' ' . $openTime);
    $close = new DateTimeImmutable(date('Y-m-d') . ' ' . $closeTime);
    if ($now >= $open && $now < $close) {
        $minutes = (int)(($close->getTimestamp() - $now->getTimestamp()) / 60);
        return ['key' => $minutes <= 60 ? 'closing' : 'open', 'label' => $minutes <= 60 ? '即將打烊' : '營業中'];
    }
    return ['key' => 'closed', 'label' => '休息中'];
}

function getTodayStoreHours() {
    global $conn;
    if (!ensureInnovationSchema()) return [];
    $day = (int)date('w');
    $stmt = $conn->prepare("SELECT * FROM store_hours WHERE day_of_week = ? ORDER BY id");
    $stmt->bind_param('i', $day);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// AI 修改：集中管理模擬智慧據點資料，支援據點頁即時監控面板與地圖切換。
function getDemoLocations() {
    return [
        ['code' => 'KHH-01', 'name_zh' => '楠梓新路智慧旗艦店', 'name_en' => 'Nanzih Xinlu Smart Flagship', 'district' => '楠梓區', 'address' => '楠梓新路 88 號（模擬營運地址）', 'hours' => '24H', 'services' => ['無障礙通道', '行動電源'], 'machine_status' => '全線運轉', 'ingredient_status' => '豚骨湯包 78%', 'amenities' => ['24H 全年無休', '智能煮麵機', '配料自動艙', '5G Wi-Fi', '強冷空調'], 'today_orders' => 146, 'last_sync' => '18 秒前', 'power_slots' => 8, 'map_query' => '高雄市楠梓區楠梓新路', 'color' => '#00eaff'],
        ['code' => 'KHH-02', 'name_zh' => '三民建工科技選物店', 'name_en' => 'Sanmin Jiangong Tech Select', 'district' => '三民區', 'address' => '建工路 215 號（模擬營運地址）', 'hours' => '06:00-24:00', 'services' => ['語音輔助', 'Wi-Fi', '內用區'], 'machine_status' => '備援機待命', 'ingredient_status' => '辣味加料 42%', 'amenities' => ['智能煮麵機', '配料自動艙', '5G Wi-Fi', '電子票券核銷'], 'today_orders' => 102, 'last_sync' => '31 秒前', 'power_slots' => 5, 'map_query' => '高雄市三民區建工路', 'color' => '#8d7bff'],
        ['code' => 'KHH-03', 'name_zh' => '大樹九大智慧據點', 'name_en' => 'Dashu Jiuda Smart Point', 'district' => '大樹區', 'address' => '九大路 36 號（模擬營運地址）', 'hours' => '08:00-22:00', 'services' => ['合作券核銷', '行李櫃', '素食設備'], 'machine_status' => '低噪模式', 'ingredient_status' => '蔬菜艙 64%', 'amenities' => ['智能煮麵機', '素食分流艙', '行李暫存', '強冷空調'], 'today_orders' => 73, 'last_sync' => '45 秒前', 'power_slots' => 3, 'map_query' => '高雄市大樹區九大路', 'color' => '#00ff9d'],
        ['code' => 'KHH-04', 'name_zh' => '湖內東方智聯無人店', 'name_en' => 'Hunei Dongfang Connected Store', 'district' => '湖內區', 'address' => '東方路 120 號（模擬營運地址）', 'hours' => '24H', 'services' => ['安全候車', '急救箱', '數位支付展示'], 'machine_status' => '夜間自動巡檢', 'ingredient_status' => '雞湯湯包 91%', 'amenities' => ['24H 全年無休', '5G Wi-Fi', '安全候車燈箱', '充電座空位'], 'today_orders' => 88, 'last_sync' => '22 秒前', 'power_slots' => 12, 'map_query' => '高雄市湖內區東方路', 'color' => '#ffcf4a'],
        ['code' => 'KHH-05', 'name_zh' => '林園沿海智能防禦店', 'name_en' => 'Linyuan Coastal Smart Shelter', 'district' => '林園區', 'address' => '沿海路 166 號（模擬營運地址）', 'hours' => '24H', 'services' => ['夜間安心連線', '補水', '自行車工具'], 'machine_status' => '強風防護啟用', 'ingredient_status' => '海鮮風味 58%', 'amenities' => ['24H 全年無休', '智能煮麵機', '防災照明', '自行車工具'], 'today_orders' => 119, 'last_sync' => '27 秒前', 'power_slots' => 6, 'map_query' => '高雄市林園區沿海路', 'color' => '#ff5f7a'],
        ['code' => 'KHH-06', 'name_zh' => '旗津三路霓虹風車店', 'name_en' => 'Cijin Sanlu Neon Windmill', 'district' => '旗津區', 'address' => '旗津三路 51 號（模擬營運地址）', 'hours' => '09:00-23:00', 'services' => ['寵物暫存展示', '拍照牆', '沖沙區'], 'machine_status' => '觀光尖峰模式', 'ingredient_status' => '乾拌麵艙 69%', 'amenities' => ['智能煮麵機', '配料自動艙', '拍照牆', '5G Wi-Fi'], 'today_orders' => 135, 'last_sync' => '14 秒前', 'power_slots' => 4, 'map_query' => '高雄市旗津區旗津三路', 'color' => '#ff4dff'],
    ];
}
?>
