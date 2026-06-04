<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as items_count,
           GROUP_CONCAT(n.name SEPARATOR ', ') as items_names
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN noodles n ON oi.noodle_id = n.id
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
    <title>My Orders - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <h1>📦 My Order History</h1>
            </div>
            <div class="nav-links">
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="cart.php">🛒 Cart</a>
                <a href="profile.php">👤 Profile</a>
                <a href="logout.php">🚪 Logout</a>
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
                            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                            <p><strong>Payment Status:</strong> 
                                <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                            <p><strong>Order Status:</strong> 
                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </p>
                            <p><strong>Pickup Code:</strong> <code class="pickup-code"><?php echo $order['pickup_code']; ?></code></p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>