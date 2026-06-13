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
        <section class="flow-progress" aria-label="Order complete">
            <!-- AI 修改：完成取餐碼步驟，讓整體流程與 draw.io 一致 -->
            <div class="flow-step is-done"><span>1</span><strong>Code</strong><small>Enter noodle code</small></div>
            <div class="flow-step is-done"><span>2</span><strong>Cart</strong><small>Confirm quantity</small></div>
            <div class="flow-step is-done"><span>3</span><strong>Pay</strong><small>Card simulation</small></div>
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
                    <small>Demo mode does not send an external email.</small>
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
                <a href="dashboard.php" class="btn btn-primary">Order More Noodles</a>
                <a href="order-history.php" class="btn btn-secondary">View My Orders</a>
            </div>
        </div>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
