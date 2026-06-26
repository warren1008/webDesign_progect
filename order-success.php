<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

if (!isset($_SESSION['last_order'])) {
    header('Location: dashboard.php');
    exit();
}

$order = $_SESSION['last_order'];
$user = getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <section class="flow-progress" aria-label="Order complete">
            <!-- AI 修改：完成取餐碼步驟，讓整體流程與 draw.io 一致 -->
            <div class="flow-step is-done"><span>1</span><strong>Code</strong><small>Enter noodle code</small></div>
            <div class="flow-step is-done"><span>2</span><strong>Cart</strong><small>Confirm quantity</small></div>
            <div class="flow-step is-done"><span>3</span><strong>Pay</strong><small>Secure payment</small></div>
            <div class="flow-step is-active"><span>4</span><strong>Pickup</strong><small>Show pickup code</small></div>
        </section>

        <div class="success-container">
            <div class="success-icon">✅</div>
            <h1>ORDER SUCCESSFUL!</h1>
            <p>Thank you for your purchase!</p>
            
            <div class="order-details-box">
                <div class="detail-row">
                    <strong>Order Number:</strong>
                    <span><?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Pickup Code:</strong>
                    <span class="pickup-code"><?php echo htmlspecialchars($order['pickup_code']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Total Paid:</strong>
                    <span>$<?php echo number_format($order['total'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Date:</strong>
                    <span><?php echo date('F d, Y h:i A'); ?></span>
                </div>
                <?php if (!empty($order['discount'])): ?>
                <div class="detail-row">
                    <strong>優惠折抵：</strong>
                    <span>-$<?php echo number_format($order['discount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['points_earned'])): ?>
                <div class="detail-row">
                    <strong>本次獲得點數：</strong>
                    <span>+<?php echo (int)$order['points_earned']; ?> P</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['invoice_carrier']) || !empty($order['tax_id'])): ?>
                <div class="detail-row">
                    <strong>收據資訊：</strong>
                    <span>
                        <?php echo !empty($order['invoice_carrier']) ? '載具 ' . htmlspecialchars($order['invoice_carrier']) : ''; ?>
                        <?php echo !empty($order['tax_id']) ? ' 統編 ' . htmlspecialchars($order['tax_id']) : ''; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="pickup-instructions">
                <h3>📋 How to pick up your noodles:</h3>
                <ol>
                    <li>Go to the pickup counter</li>
                    <li>Enter your pickup code on the screen: <strong><?php echo $order['pickup_code']; ?></strong></li>
                    <li>Wait for your order number to appear</li>
                    <li>Take your bag of noodles and enjoy!</li>
                </ol>
            </div>

            <?php if (!empty($order['items'])): ?>
            <div class="order-pickup-items">
                <!-- AI 修改：訂單成功頁補上含加料的取餐內容，讓購買流程前後一致。 -->
                <h3>本次取餐內容</h3>
                <?php foreach ($order['items'] as $item): ?>
                    <div class="pickup-item-row">
                        <span><?php echo (int)$item['quantity']; ?> 份 <?php echo htmlspecialchars($item['name']); ?></span>
                        <?php if (!empty($item['customization'])): ?>
                            <small><?php echo htmlspecialchars(customizationSummary($item['customization'])); ?></small>
                        <?php endif; ?>
                        <strong>$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="pickup-verification">
                <!-- AI 修改：補上流程圖要求的 QR 取貨憑證與電子收據情境 -->
                <div class="pickup-qr-card">
                    <div class="pickup-qr" data-pickup-qr="<?php echo htmlspecialchars($order['pickup_code']); ?>" aria-label="Pickup QR token"></div>
                    <p>Scan at the unmanned pickup locker</p>
                </div>
                <div class="digital-receipt">
                    <span>EMAIL CONFIRMATION</span>
                    <h3>Digital receipt ready</h3>
                    <p>Order confirmation is prepared for <?php echo htmlspecialchars($user['email']); ?>.</p>
                    <small><?php echo htmlspecialchars($order['receipt_note'] ?? 'A digital receipt is prepared for this order.'); ?></small>
                </div>
            </div>

            <div class="pickup-tracker" data-pickup-tracker>
                <!-- AI 修改：新增取餐狀態模擬，強化無人商店情境展示 -->
                <div class="pickup-stage is-active">Payment verified</div>
                <div class="pickup-stage">Locker unlocking</div>
                <div class="pickup-stage">Ready for pickup</div>
                <div class="pickup-stage">Digital receipt ready</div>
            </div>
            
            <div class="actions">
                <a href="kitchen-status.php?order=<?php echo urlencode($order['order_number']); ?>" class="btn btn-success">Track Kitchen & Locker</a>
                <a href="dashboard.php" class="btn btn-primary">Order More Noodles</a>
                <a href="order-history.php" class="btn btn-secondary">View My Orders</a>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
