<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
ensureInnovationSchema();

$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as items_count,
           GROUP_CONCAT(CONCAT(n.name, IF(oi.customization_json IS NULL OR oi.customization_json = '', '', '（含加料）')) SEPARATOR ', ') as items_names,
           MAX(r.id) AS review_id
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN noodles n ON oi.noodle_id = n.id
    LEFT JOIN order_reviews r ON r.order_id = o.id AND r.user_id = o.user_id
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <header>
            <div class="logo">
                <h1 data-en="My Order History" data-zh="我的訂單紀錄">My Order History</h1>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" data-en="Dashboard" data-zh="點餐台">Dashboard</a>
                <a href="cart.php" data-en="Cart" data-zh="購物車">Cart</a>
                <a href="profile.php" data-en="Profile" data-zh="會員資料">Profile</a>
                <a href="logout.php" data-en="Logout" data-zh="登出">Logout</a>
            </div>
        </header>
        
        <?php if ($orders->num_rows == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders. Start shopping now!</p>
                <a href="dashboard.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php while ($order = $orders->fetch_assoc()): ?>
                <?php
                    $expired = in_array($order['order_status'], ['cancelled'], true)
                        || (strtotime($order['created_at']) < strtotime('-24 hours') && !in_array($order['order_status'], ['ready', 'completed'], true));
                    $statusZh = [
                        'pending' => '待處理', 'confirmed' => '已確認', 'preparing' => '製作中',
                        'ready' => '可取餐', 'completed' => '已完成', 'cancelled' => '已取消'
                    ][$order['order_status']] ?? $order['order_status'];
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">
                            <strong>Order #:</strong> <?php echo htmlspecialchars($order['order_number']); ?>
                        </div>
                        <div class="order-date">
                            <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                        </div>
                    </div>
                    <div class="order-details">
                        <div class="order-info">
                            <p><strong>Items:</strong> <?php echo $order['items_count']; ?> item(s)</p>
                            <p><strong>Products:</strong> <?php echo htmlspecialchars($order['items_names'] ?: 'Unavailable'); ?></p>
                            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                            <p><strong>Payment Status:</strong> 
                                <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                            <p><strong>Order Status:</strong> 
                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo htmlspecialchars($statusZh); ?>
                                </span>
                            </p>
                            <?php if (!$expired): ?>
                                <p class="pickup-code-row"><strong>取餐碼：</strong> <code class="pickup-code"><?php echo htmlspecialchars($order['pickup_code']); ?></code>
                                    <button type="button" class="btn btn-small btn-secondary"
                                            data-show-pickup-qr data-pickup-code="<?php echo htmlspecialchars($order['pickup_code']); ?>">手機掃碼取餐</button>
                                </p>
                            <?php else: ?>
                                <p class="expired-order-note">此訂單已失效，取餐碼已隱藏。</p>
                            <?php endif; ?>
                            <?php if (in_array($order['order_status'], ['confirmed', 'preparing'], true)): ?>
                                <p class="robot-cooking-note">● 機器人正在為您烹調中，請留意取餐狀態。</p>
                            <?php endif; ?>
                            <p>
                                <?php if ($order['order_status'] === 'completed'): ?>
                                    <button type="button" class="btn btn-small btn-secondary"
                                            data-review-order="<?php echo (int)$order['id']; ?>"
                                            <?php echo $order['review_id'] ? 'disabled' : ''; ?>>
                                        <?php echo $order['review_id'] ? '已完成評價' : '★ 評價這碗麵（賺 10 點）'; ?>
                                    </button>
                                <?php else: ?>
                                    <a class="btn btn-small btn-secondary" href="kitchen-status.php?order=<?php echo urlencode($order['order_number']); ?>">製作與取餐資訊</a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
