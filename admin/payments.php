<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

$payments = $conn->query("
    SELECT p.*, o.order_number, u.username 
    FROM payments p
    JOIN orders o ON p.order_id = o.id
    JOIN users u ON o.user_id = u.id
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Logs - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>💳 Payment Transaction Logs</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="payments.php">Payments</a>
                <a href="profile.php">Profile</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Card Type</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($payment = $payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($payment['order_number']); ?></td>
                    <td><?php echo htmlspecialchars($payment['username']); ?></td>
                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                    <td><?php echo ucfirst($payment['card_type'] ?? 'N/A'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                            <?php echo ucfirst($payment['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="../assets/app.js"></script>
</body>
</html>
