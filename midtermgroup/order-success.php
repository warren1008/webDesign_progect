<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

if (!isset($_SESSION['last_order'])) {
    header('Location: dashboard.php');
    exit();
}

$order = $_SESSION['last_order'];
unset($_SESSION['last_order']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
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
            
            <div class="actions">
                <a href="dashboard.php" class="btn btn-primary">Order More Noodles</a>
                <a href="order-history.php" class="btn btn-secondary">View My Orders</a>
            </div>
        </div>
    </div>
</body>
</html>