<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

function apiResponse($success, $data = [], $status = 200) {
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE);
    exit();
}

function apiInput() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $decoded = json_decode((string)file_get_contents('php://input'), true);
        return is_array($decoded) ? $decoded : [];
    }
    return $_POST;
}

function apiRequireLogin() {
    if (empty($_SESSION['user_id'])) {
        apiResponse(false, ['message' => '請先登入後再操作。'], 401);
    }
}

function apiRequireAdmin() {
    apiRequireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        apiResponse(false, ['message' => '管理員權限不足。'], 403);
    }
}

function apiVerifyCsrf($input) {
    if (!verifyCsrfToken($input['csrf_token'] ?? '')) {
        apiResponse(false, ['message' => '頁面已逾時，請重新整理後再試。'], 419);
    }
}

$input = apiInput();
$action = (string)($input['action'] ?? '');

try {
    if ($action === 'redeem_reward') {
        apiRequireLogin();
        apiVerifyCsrf($input);
        $result = redeemReward((int)$_SESSION['user_id'], (int)($input['reward_id'] ?? 0));
        if (!$result['success']) apiResponse(false, ['message' => $result['message']], 422);
        apiResponse(true, [
            'message' => '兌換成功，兌換券已加入會員中心。',
            'code' => $result['code'],
            'balance' => getUserPointBalance((int)$_SESSION['user_id']),
        ]);
    }

    if ($action === 'add_bundle') {
        apiRequireLogin();
        apiVerifyCsrf($input);
        $code = strtoupper(trim((string)($input['code'] ?? '')));
        $quantity = max(1, min(6, (int)($input['quantity'] ?? 1)));
        $noodle = getNoodleByCode($code);
        if (!$noodle) apiResponse(false, ['message' => '找不到指定商品。'], 404);
        $current = getCartNoodleQuantity((int)$noodle['id']);
        if ($current + $quantity > (int)$noodle['stock']) {
            apiResponse(false, ['message' => '商品庫存不足。'], 422);
        }
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
        $key = (string)(int)$noodle['id'];
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$key] = [
                'id' => (int)$noodle['id'],
                'code' => $noodle['code'],
                'name' => $noodle['name'],
                'price' => (float)$noodle['price'],
                'quantity' => $quantity,
            ];
        }
        apiResponse(true, ['message' => '商品已加入購物車。', 'cart_count' => getCartCount()]);
    }

    if ($action === 'reorder_last') {
        apiRequireLogin();
        apiVerifyCsrf($input);
        $stmt = $conn->prepare("SELECT id FROM orders WHERE user_id = ? AND payment_status = 'paid' ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $last = $stmt->get_result()->fetch_assoc();
        if (!$last) apiResponse(false, ['message' => '目前沒有可複製的歷史訂單。'], 404);
        $stmt = $conn->prepare("SELECT oi.*, n.code, n.name, n.stock
            FROM order_items oi JOIN noodles n ON n.id = oi.noodle_id WHERE oi.order_id = ?");
        $stmt->bind_param('i', $last['id']);
        $stmt->execute();
        $items = $stmt->get_result();
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
        $added = 0;
        while ($item = $items->fetch_assoc()) {
            $quantity = min((int)$item['quantity'], max(0, (int)$item['stock'] - getCartNoodleQuantity((int)$item['noodle_id'])));
            if ($quantity < 1) continue;
            $customization = json_decode((string)($item['customization_json'] ?? ''), true) ?: [];
            $key = cartItemKey((int)$item['noodle_id'], $customization);
            if (isset($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$key] = [
                    'id' => (int)$item['noodle_id'],
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'price' => (float)$item['price'],
                    'quantity' => $quantity,
                    'customization' => $customization,
                ];
            }
            $added += $quantity;
        }
        if (!$added) apiResponse(false, ['message' => '上一單商品目前皆無庫存。'], 422);
        apiResponse(true, ['message' => '上一單已加入購物車。', 'cart_count' => getCartCount()]);
    }

    if ($action === 'review_order') {
        apiRequireLogin();
        apiVerifyCsrf($input);
        ensureInnovationSchema();
        $orderId = (int)($input['order_id'] ?? 0);
        $rating = max(1, min(5, (int)($input['rating'] ?? 5)));
        $comment = mb_substr(trim((string)($input['comment'] ?? '')), 0, 255);
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND order_status = 'completed'");
        $stmt->bind_param('ii', $orderId, $_SESSION['user_id']);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            apiResponse(false, ['message' => '只有已完成的本人訂單可以評價。'], 422);
        }
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO order_reviews (order_id,user_id,rating,comment) VALUES (?,?,?,?)");
            $stmt->bind_param('iiis', $orderId, $_SESSION['user_id'], $rating, $comment);
            $stmt->execute();
            if (!changeUserPoints((int)$_SESSION['user_id'], 10, 'earn', '完成拉麵評價獎勵', $orderId)) {
                throw new Exception('點數寫入失敗。');
            }
            $conn->commit();
        } catch (Throwable $error) {
            $conn->rollback();
            apiResponse(false, ['message' => $error->getCode() === 1062 ? '這筆訂單已經評價過了。' : $error->getMessage()], 422);
        }
        apiResponse(true, ['message' => '感謝評價，已獲得 10 點。', 'balance' => getUserPointBalance((int)$_SESSION['user_id'])]);
    }

    if ($action === 'partner_unlock') {
        apiRequireLogin();
        apiVerifyCsrf($input);
        $stmt = $conn->prepare("SELECT pickup_code FROM orders
            WHERE user_id = ? AND order_status <> 'cancelled' AND created_at >= CURDATE()
            ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order) apiResponse(false, ['message' => '尚未偵測到今日拉麵訂單，請先至點餐台消費。'], 404);
        apiResponse(true, ['message' => '已解鎖：出示取餐碼即可使用合作優惠。', 'pickup_code' => $order['pickup_code']]);
    }

    if ($action === 'support_case') {
        apiRequireLogin();
        ensureInnovationSchema();
        $orderNumber = mb_substr(trim((string)($input['order_number'] ?? '')), 0, 50);
        $issueType = 'machine_no_dispense';
        $stmt = $conn->prepare("INSERT INTO support_cases (user_id,order_number,issue_type) VALUES (?,?,?)");
        $stmt->bind_param('iss', $_SESSION['user_id'], $orderNumber, $issueType);
        $stmt->execute();
        apiResponse(true, ['message' => '已建立客服案件 CS-' . str_pad((string)$conn->insert_id, 4, '0', STR_PAD_LEFT) . '。']);
    }

    if (str_starts_with($action, 'admin_')) {
        apiRequireAdmin();
        apiVerifyCsrf($input);
    }

    if ($action === 'admin_product_update') {
        $id = (int)($input['id'] ?? 0);
        $price = max(0, (float)($input['price'] ?? 0));
        $stock = max(0, (int)($input['stock'] ?? 0));
        $stmt = $conn->prepare("UPDATE noodles SET price = ?, stock = ? WHERE id = ?");
        $stmt->bind_param('dii', $price, $stock, $id);
        $stmt->execute();
        apiResponse(true, ['message' => '商品資料已更新。']);
    }

    if ($action === 'admin_product_delete') {
        $id = (int)($input['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM noodles WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        apiResponse(true, ['message' => '商品已刪除。']);
    }

    if ($action === 'admin_user_delete') {
        $id = (int)($input['id'] ?? 0);
        if ($id === (int)$_SESSION['user_id']) apiResponse(false, ['message' => '不可刪除目前管理員。'], 422);
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role <> 'admin'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        apiResponse(true, ['message' => '使用者數位通行權限已移除。']);
    }

    if ($action === 'admin_order_status') {
        $id = (int)($input['id'] ?? 0);
        $status = (string)($input['status'] ?? '');
        $allowed = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) apiResponse(false, ['message' => '無效狀態。'], 422);
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        apiResponse(true, ['message' => '出餐協定已同步。', 'status' => $status]);
    }

    apiResponse(false, ['message' => '未知操作。'], 404);
} catch (Throwable $error) {
    apiResponse(false, ['message' => '系統暫時無法完成操作：' . $error->getMessage()], 500);
}
