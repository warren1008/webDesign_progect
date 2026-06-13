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
function createOrder($user_id, $cart, $payment_method, $card_type = null) {
    global $conn;
    
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    $order_number = generateOrderNumber();
    $pickup_code = generatePickupCode();
    
    $conn->begin_transaction();
    
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_amount, payment_method, pickup_code, order_status, payment_status) VALUES (?, ?, ?, ?, ?, 'confirmed', 'paid')");
        $stmt->bind_param("sidss", $order_number, $user_id, $total, $payment_method, $pickup_code);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Insert order items
        foreach ($cart as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, noodle_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            // AI 修改：建立訂單時同步檢查庫存，避免超賣
            $stmt2 = $conn->prepare("UPDATE noodles SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $stmt2->bind_param("iii", $item['quantity'], $item['id'], $item['quantity']);
            $stmt2->execute();
            if ($stmt2->affected_rows === 0) {
                throw new Exception($item['name'] . ' is out of stock or quantity changed.');
            }
        }
        
        $conn->commit();
        return ['success' => true, 'order_id' => $order_id, 'order_number' => $order_number, 'pickup_code' => $pickup_code];
        
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
?>
