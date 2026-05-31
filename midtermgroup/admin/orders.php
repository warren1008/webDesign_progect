<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $order_status = $_POST['order_status'];
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $order_status, $order_id);
    $stmt->execute();
}

// Get all orders
$orders = $conn->query("
    SELECT o.*, u.username, u.email,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 Manage Customer Orders</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>
        
        <div class="orders-admin">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Pickup Code</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($order['username']); ?><br>
                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                        </td>
                        <td><?php echo date('M d, h:i A', strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['item_count']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </td>
                        <td><code><?php echo $order['pickup_code']; ?></code></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="order_status" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $order['order_status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="preparing" <?php echo $order['order_status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="ready" <?php echo $order['order_status'] == 'ready' ? 'selected' : ''; ?>>Ready</option>
                                    <option value="completed" <?php echo $order['order_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>