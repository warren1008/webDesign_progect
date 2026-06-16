<?php
require_once __DIR__ . '/config.php';

// AI 修改：依目前頁面所在目錄產生正確導向路徑，避免前台頁面被導到錯誤的 ../login.php
function appPath($path) {
    $inAdmin = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
    return ($inAdmin ? '../' : '') . ltrim($path, '/');
}

function redirectTo($path) {
    header('Location: ' . appPath($path));
    exit();
}

// AI 修改：共用 CSRF Token，保護新增的會員與點餐表單
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirectTo('login.php');
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        redirectTo('dashboard.php');
    }
}

// AI 修改：資料庫不可用時仍可用展示資料完成點餐流程 demo
function getDemoNoodles() {
    return [
        ['id' => 1, 'code' => 'N001', 'name' => 'Shin Ramyun', 'brand' => 'Nongshim', 'price' => 5.99, 'stock' => 50],
        ['id' => 2, 'code' => 'N002', 'name' => 'Indomie Mi Goreng', 'brand' => 'Indomie', 'price' => 3.99, 'stock' => 100],
        ['id' => 3, 'code' => 'N003', 'name' => 'Mama Tom Yum', 'brand' => 'Mama', 'price' => 2.99, 'stock' => 75],
        ['id' => 4, 'code' => 'N004', 'name' => 'Samyang Buldak', 'brand' => 'Samyang', 'price' => 6.99, 'stock' => 30],
        ['id' => 5, 'code' => 'N005', 'name' => 'Maruchan Chicken', 'brand' => 'Maruchan', 'price' => 1.99, 'stock' => 200],
        ['id' => 6, 'code' => 'N006', 'name' => 'Cup Noodles', 'brand' => 'Nissin', 'price' => 2.49, 'stock' => 150],
        ['id' => 7, 'code' => 'N007', 'name' => 'Sapporo Ichiban', 'brand' => 'Sapporo', 'price' => 2.29, 'stock' => 120],
        ['id' => 8, 'code' => 'N008', 'name' => 'Nissin Raoh', 'brand' => 'Nissin', 'price' => 3.49, 'stock' => 60],
    ];
}

function findDemoNoodle($matcher) {
    foreach (getDemoNoodles() as $noodle) {
        if ($matcher($noodle)) {
            return $noodle;
        }
    }
    return null;
}

// Get user by ID
function getUserById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get noodle by code
function getNoodleByCode($code) {
    global $conn, $db_error;
    $code = strtoupper(trim($code));

    if (empty($db_error)) {
        $stmt = $conn->prepare("SELECT * FROM noodles WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $noodle = $stmt->get_result()->fetch_assoc();
        if ($noodle) {
            return $noodle;
        }
    }

    return findDemoNoodle(function ($item) use ($code) {
        return $item['code'] === $code;
    });
}

// Get noodle by ID
function getNoodleById($id) {
    global $conn, $db_error;
    $id = (int)$id;

    if (empty($db_error)) {
        $stmt = $conn->prepare("SELECT * FROM noodles WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $noodle = $stmt->get_result()->fetch_assoc();
        if ($noodle) {
            return $noodle;
        }
    }

    return findDemoNoodle(function ($item) use ($id) {
        return (int)$item['id'] === $id;
    });
}

// Update stock
function updateStock($noodle_id, $quantity) {
    global $conn;
    $stmt = $conn->prepare("UPDATE noodles SET stock = stock - ? WHERE id = ? AND stock >= ?");
    $stmt->bind_param("iii", $quantity, $noodle_id, $quantity);
    return $stmt->execute() && $stmt->affected_rows > 0;
}

// Generate unique order number
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Generate pickup code
function generatePickupCode() {
    return strtoupper(substr(md5(uniqid()), 0, 8));
}

function generateLockerNumber($orderId) {
    return 'L-' . str_pad((string)(((int)$orderId % 24) + 1), 2, '0', STR_PAD_LEFT);
}

// AI 修改：客製化選項由後端重新計價，避免前端竄改價格
function getCustomizationCatalog() {
    return [
        'broth' => [
            'classic' => ['label' => 'Classic Shoyu', 'price' => 0.00],
            'tonkotsu' => ['label' => 'Creamy Tonkotsu', 'price' => 1.40],
            'miso' => ['label' => 'Roasted Miso', 'price' => 1.10],
            'tom-yum' => ['label' => 'Tom Yum Citrus', 'price' => 1.25],
        ],
        'noodle' => [
            'regular' => ['label' => 'Regular Noodles', 'price' => 0.00],
            'thin' => ['label' => 'Thin Noodles', 'price' => 0.35],
            'thick' => ['label' => 'Thick Noodles', 'price' => 0.55],
        ],
        'spice' => [
            '0' => ['label' => 'No Spice', 'price' => 0.00],
            '1' => ['label' => 'Mild', 'price' => 0.00],
            '2' => ['label' => 'Medium', 'price' => 0.20],
            '3' => ['label' => 'Hot', 'price' => 0.35],
            '4' => ['label' => 'Fire Challenge', 'price' => 0.60],
        ],
        'size' => [
            'regular' => ['label' => 'Regular Bowl', 'price' => 0.00],
            'large' => ['label' => 'Large Bowl', 'price' => 1.50],
        ],
        'toppings' => [
            'egg' => ['label' => 'Ajitama Egg', 'price' => 0.90],
            'corn' => ['label' => 'Sweet Corn', 'price' => 0.55],
            'seaweed' => ['label' => 'Roasted Seaweed', 'price' => 0.45],
            'chashu' => ['label' => 'Chashu', 'price' => 1.60],
            'scallion' => ['label' => 'Scallion', 'price' => 0.35],
        ],
    ];
}

function buildCustomization($input) {
    $catalog = getCustomizationCatalog();
    $selection = [];
    $extra = 0.0;

    foreach (['broth', 'noodle', 'spice', 'size'] as $group) {
        $value = (string)($input[$group] ?? '');
        if (!isset($catalog[$group][$value])) {
            return ['success' => false, 'message' => 'Please choose a valid customization option.'];
        }
        $selection[$group] = [
            'key' => $value,
            'label' => $catalog[$group][$value]['label'],
        ];
        $extra += $catalog[$group][$value]['price'];
    }

    $selection['toppings'] = [];
    $toppings = array_unique(array_map('strval', (array)($input['toppings'] ?? [])));
    foreach ($toppings as $value) {
        if (!isset($catalog['toppings'][$value])) {
            continue;
        }
        $selection['toppings'][] = [
            'key' => $value,
            'label' => $catalog['toppings'][$value]['label'],
        ];
        $extra += $catalog['toppings'][$value]['price'];
    }

    return ['success' => true, 'selection' => $selection, 'extra' => round($extra, 2)];
}

function customizationSummary($customization) {
    if (empty($customization) || !is_array($customization)) {
        return '';
    }

    $parts = [];
    foreach (['broth', 'noodle', 'spice', 'size'] as $group) {
        if (!empty($customization[$group]['label'])) {
            $parts[] = $customization[$group]['label'];
        }
    }
    if (!empty($customization['toppings'])) {
        $parts[] = implode(', ', array_column($customization['toppings'], 'label'));
    }
    return implode(' / ', $parts);
}

// AI 修改：一般點餐頁加料選項由後端統一提供，避免前端自行決定價格。
function getNoodleToppingCatalog() {
    global $conn, $db_error;
    $fallback = [
        ['code' => 'large-noodle', 'name_zh' => '加大麵量', 'name_en' => 'Large Noodle Boost', 'description' => '多 35% 麵量，適合正餐份量。', 'price' => 0.79, 'stock' => 80, 'category' => 'size'],
        ['code' => 'ajitama', 'name_zh' => '半熟溏心蛋', 'name_en' => 'Ajitama Egg', 'description' => '自助機台冷藏艙取出的半熟蛋。', 'price' => 0.95, 'stock' => 64, 'category' => 'protein'],
        ['code' => 'cheese', 'name_zh' => '融化起司片', 'name_en' => 'Melted Cheese', 'description' => '讓辣味泡麵變得更濃郁。', 'price' => 0.70, 'stock' => 72, 'category' => 'protein'],
        ['code' => 'chashu', 'name_zh' => '炙燒叉燒片', 'name_en' => 'Torched Chashu', 'description' => '無人加熱艙模擬炙燒風味。', 'price' => 1.45, 'stock' => 42, 'category' => 'protein'],
        ['code' => 'corn', 'name_zh' => '甜玉米粒', 'name_en' => 'Sweet Corn', 'description' => '清甜口感，適合雞湯與味噌。', 'price' => 0.55, 'stock' => 95, 'category' => 'vegetable'],
        ['code' => 'seaweed', 'name_zh' => '脆海苔片', 'name_en' => 'Crispy Seaweed', 'description' => '取餐時獨立封裝避免軟化。', 'price' => 0.45, 'stock' => 88, 'category' => 'vegetable'],
        ['code' => 'scallion', 'name_zh' => '青蔥增量', 'name_en' => 'Extra Scallion', 'description' => '提升香氣與清爽感。', 'price' => 0.35, 'stock' => 110, 'category' => 'vegetable'],
        ['code' => 'spicy-oil', 'name_zh' => '霓虹辣油', 'name_en' => 'Neon Chili Oil', 'description' => '夜間限定辣油，帶微麻尾韻。', 'price' => 0.40, 'stock' => 58, 'category' => 'sauce'],
    ];

    if (!empty($db_error) || !function_exists('ensureInnovationSchema') || !ensureInnovationSchema()) {
        return $fallback;
    }

    $result = $conn->query("SELECT code,name_zh,name_en,description,price,stock,category FROM topping_options WHERE active = 1 ORDER BY FIELD(category,'size','protein','vegetable','sauce','limited'), price");
    if (!$result) {
        return $fallback;
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function buildNoodleAddons($selectedCodes, $quantity = 1) {
    $catalog = [];
    foreach (getNoodleToppingCatalog() as $item) {
        $catalog[$item['code']] = $item;
    }

    $selectedCodes = array_values(array_unique(array_map(
        static fn($code) => preg_replace('/[^a-z0-9-]/', '', strtolower((string)$code)),
        (array)$selectedCodes
    )));
    $selectedCodes = array_slice(array_filter($selectedCodes), 0, 6);

    $addons = [];
    $extra = 0.0;
    foreach ($selectedCodes as $code) {
        if (empty($catalog[$code])) {
            continue;
        }
        $item = $catalog[$code];
        if ((int)$item['stock'] < max(1, (int)$quantity)) {
            return ['success' => false, 'message' => $item['name_zh'] . ' 庫存不足，請先取消該加料。'];
        }
        $addons[] = [
            'key' => $code,
            'label' => $item['name_zh'],
            'label_en' => $item['name_en'],
            'price' => (float)$item['price'],
            'category' => $item['category'],
        ];
        $extra += (float)$item['price'];
    }

    return [
        'success' => true,
        'selection' => ['toppings' => $addons],
        'extra' => round($extra, 2),
    ];
}

function reduceToppingStock($customization, $quantity) {
    global $conn, $db_error;
    if (!empty($db_error) || empty($customization['toppings']) || !function_exists('ensureInnovationSchema') || !ensureInnovationSchema()) {
        return true;
    }
    foreach ($customization['toppings'] as $topping) {
        if (empty($topping['key'])) {
            continue;
        }
        $code = (string)$topping['key'];
        $qty = (int)$quantity;
        $stmt = $conn->prepare("UPDATE topping_options SET stock = stock - ? WHERE code = ? AND stock >= ?");
        $stmt->bind_param('isi', $qty, $code, $qty);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new Exception(($topping['label'] ?? '加料') . ' is out of stock or quantity changed.');
        }
    }
    return true;
}

function ensureOrderCustomizationSchema() {
    global $conn;
    static $checked = false;
    if ($checked) {
        return true;
    }
    $checked = true;

    $result = $conn->query("SHOW COLUMNS FROM order_items LIKE 'customization_json'");
    if ($result && $result->num_rows > 0) {
        return true;
    }
    return (bool)$conn->query("ALTER TABLE order_items ADD customization_json TEXT NULL AFTER price");
}

function cartItemKey($noodleId, $customization = []) {
    if (empty($customization)) {
        return (string)(int)$noodleId;
    }
    return 'custom-' . (int)$noodleId . '-' . substr(hash('sha256', json_encode($customization)), 0, 12);
}

// AI 修改：檢查付款月份格式與是否已過期，避免只驗證字串外觀
function isValidPaymentExpiry($cardExpiry) {
    if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $cardExpiry, $matches)) {
        return false;
    }

    $month = (int)$matches[1];
    $year = 2000 + (int)$matches[2];
    $expiryEnd = new DateTimeImmutable(sprintf('%04d-%02d-01 23:59:59', $year, $month));
    $expiryEnd = $expiryEnd->modify('last day of this month');

    return $expiryEnd >= new DateTimeImmutable('today');
}

// Process payment (simulation - for demo only)
function processPayment($amount, $cardNumber, $cardExpiry, $cardCVV, $cardType = '') {
    // AI 修改：讓付款 Demo 與畫面上的卡別選項一致，避免 Amex 選項永遠失敗
    $cardNumber = preg_replace('/\s/', '', $cardNumber);
    $firstDigit = substr($cardNumber, 0, 1);
    $cardType = strtolower($cardType);
    
    if (!isValidPaymentExpiry($cardExpiry)) {
        return ['success' => false, 'message' => 'Invalid or expired card date. Use a future MM/YY value.'];
    }

    $isVisa = $cardType === 'visa' && $firstDigit === '4' && strlen($cardNumber) === 16 && strlen($cardCVV) === 3;
    $isMastercard = $cardType === 'mastercard' && $firstDigit === '5' && strlen($cardNumber) === 16 && strlen($cardCVV) === 3;
    $isAmex = $cardType === 'amex' && $firstDigit === '3' && strlen($cardNumber) === 15 && strlen($cardCVV) === 4;

    // Demo: Accept valid-looking cards by selected card type
    if ($isVisa || $isMastercard || $isAmex) {
        return ['success' => true, 'transaction_id' => 'TXN-' . strtoupper(uniqid())];
    }
    
    return ['success' => false, 'message' => 'Invalid demo card details. Visa starts with 4, Mastercard starts with 5, Amex starts with 3.'];
}

// Create order
function createOrder($user_id, $cart, $payment_method, $card_type = null, $pricing = null) {
    global $conn;
    if (!ensureOrderCustomizationSchema()) {
        return ['success' => false, 'message' => 'The database update for custom orders is not installed.'];
    }
    
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $pricing = is_array($pricing) ? $pricing : [];
    $total = (float)($pricing['final_total'] ?? $subtotal);
    $discount = (float)($pricing['discount'] ?? 0);
    $pointsUsed = (int)($pricing['points_used'] ?? 0);
    $pointsEarned = (int)($pricing['points_earned'] ?? 0);
    $promotionSummary = implode(', ', array_map(
        static fn($promotion) => $promotion['title_zh'] ?? $promotion['title'] ?? '',
        $pricing['applied_promotions'] ?? []
    ));
    
    $order_number = generateOrderNumber();
    $pickup_code = generatePickupCode();
    
    $conn->begin_transaction();
    
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders
            (order_number,user_id,total_amount,subtotal_amount,discount_amount,points_used,points_earned,promotion_summary,payment_method,pickup_code,order_status,payment_status)
            VALUES (?,?,?,?,?,?,?,?,?,?,'confirmed','paid')");
        $stmt->bind_param(
            "sidddiisss",
            $order_number,
            $user_id,
            $total,
            $subtotal,
            $discount,
            $pointsUsed,
            $pointsEarned,
            $promotionSummary,
            $payment_method,
            $pickup_code
        );
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Insert order items
        foreach ($cart as $item) {
            $customizationJson = empty($item['customization'])
                ? null
                : json_encode($item['customization'], JSON_UNESCAPED_UNICODE);
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, noodle_id, quantity, price, customization_json) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiids", $order_id, $item['id'], $item['quantity'], $item['price'], $customizationJson);
            $stmt->execute();
            
            // AI 修改：建立訂單時同步檢查庫存，避免超賣
            $stmt2 = $conn->prepare("UPDATE noodles SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $stmt2->bind_param("iii", $item['quantity'], $item['id'], $item['quantity']);
            $stmt2->execute();
            if ($stmt2->affected_rows === 0) {
                throw new Exception($item['name'] . ' is out of stock or quantity changed.');
            }
            reduceToppingStock($item['customization'] ?? [], (int)$item['quantity']);
        }
        
        $conn->commit();
        return [
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number,
            'pickup_code' => $pickup_code,
            'pricing' => $pricing,
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Record payment
function recordPayment($order_id, $amount, $card_type, $status, $transaction_id = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO payments (order_id, transaction_id, amount, card_type, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $order_id, $transaction_id, $amount, $card_type, $status);
    return $stmt->execute();
}

// Get cart count
function getCartCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function getCartNoodleQuantity($noodleId) {
    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        if ((int)($item['id'] ?? 0) === (int)$noodleId) {
            $count += (int)($item['quantity'] ?? 0);
        }
    }
    return $count;
}

// Get cart total
function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

function getOrderForUser($orderNumber, $userId) {
    global $conn, $db_error;
    if (!empty($db_error)) {
        $lastOrder = $_SESSION['last_order'] ?? null;
        if ($lastOrder && ($lastOrder['order_number'] ?? '') === $orderNumber) {
            return [
                'id' => abs(crc32($orderNumber)),
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'pickup_code' => $lastOrder['pickup_code'] ?? 'DEMO2026',
                'order_status' => 'confirmed',
                'payment_status' => 'paid',
                'total_amount' => $lastOrder['total'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'email' => '',
            ];
        }
        return null;
    }

    $stmt = $conn->prepare("
        SELECT o.*, u.email
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.order_number = ? AND o.user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("si", $orderNumber, $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

require_once __DIR__ . '/innovation.php';
?>
