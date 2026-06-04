<?php
require_once __DIR__ . '/config.php';

// Redirect if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ../dashboard.php');
        exit();
    }
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
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM noodles WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get noodle by ID
function getNoodleById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM noodles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
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

// Process payment (simulation - for demo only)
function processPayment($amount, $cardNumber, $cardExpiry, $cardCVV) {
    // Remove spaces from card number
    $cardNumber = preg_replace('/\s/', '', $cardNumber);
    $firstDigit = substr($cardNumber, 0, 1);
    
    // Demo: Accept any valid-looking card
    if (($firstDigit == '4' || $firstDigit == '5') && strlen($cardNumber) >= 15 && strlen($cardNumber) <= 16 && strlen($cardCVV) >= 3) {
        return ['success' => true, 'transaction_id' => 'TXN-' . strtoupper(uniqid())];
    }
    
    return ['success' => false, 'message' => 'Invalid card details. Use card starting with 4 or 5.'];
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
            
            // Update stock
            $stmt2 = $conn->prepare("UPDATE noodles SET stock = stock - ? WHERE id = ?");
            $stmt2->bind_param("ii", $item['quantity'], $item['id']);
            $stmt2->execute();
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