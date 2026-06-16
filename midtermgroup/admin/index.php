<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

// AI 修改：以 Session 模擬即時交易，不污染正式訂單與付款資料。
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate_live_sale'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['admin_demo_orders'] = (int)($_SESSION['admin_demo_orders'] ?? 0) + 1;
        $_SESSION['admin_demo_sales'] = (float)($_SESSION['admin_demo_sales'] ?? 0) + 12.44;
        header('Location: index.php?demo_live=1');
        exit();
    }
}

// Get stats
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$total_noodles = $conn->query("SELECT COUNT(*) as count FROM noodles")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM noodles WHERE stock < 10")->fetch_assoc()['count'];
$total_orders += (int)($_SESSION['admin_demo_orders'] ?? 0);
$total_sales = (float)($total_sales ?? 0) + (float)($_SESSION['admin_demo_sales'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Noodle Store</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
    <div class="container admin-container">
        <header>
            <div class="logo">
                <h1>🛠️ Admin Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="analytics.php">Analytics</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="payments.php">Payments</a>
                <a href="marketing.php">活動管理</a>
                <a href="profile.php">Profile</a>
                <a href="../logout.php">Logout</a>
            </div>
        </header>
        <?php if (isset($_GET['demo_live'])): ?>
            <div class="success">已接收一筆展示用機台付款，未寫入正式交易資料。</div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card" data-admin-orders>
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $total_orders; ?></p>
                </div>
            </div>
            <div class="stat-card" data-admin-sales>
                <span class="live-monitor">● Live</span>
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Total Sales</h3>
                    <p class="stat-number" data-usd-value="<?php echo number_format($total_sales ?? 0, 2, '.', ''); ?>">$<?php echo number_format($total_sales ?? 0, 2); ?></p>
                </div>
                <form method="POST" class="live-sale-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <button type="submit" name="simulate_live_sale" class="btn btn-secondary btn-small">模擬新訂單結帳</button>
                </form>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $total_users; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🍜</div>
                <div class="stat-info">
                    <h3>Noodle Products</h3>
                    <p class="stat-number"><?php echo $total_noodles; ?></p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3>Pending Orders</h3>
                    <p class="stat-number"><?php echo $pending_orders; ?></p>
                </div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3>Low Stock Items</h3>
                    <p class="stat-number"><?php echo $low_stock; ?></p>
                </div>
            </div>
        </div>
        
        <div class="admin-sections">
            <div class="admin-section">
                <h2>活動與會員營運</h2>
                <p>管理促銷活動、點數獎品、合作店家與服務時間。</p>
                <a href="marketing.php" class="btn btn-success">開啟活動管理</a>
            </div>
            <div class="admin-section">
                <h2>Smart Analytics</h2>
                <p>Review product demand, revenue, stock risk and automated restock suggestions.</p>
                <a href="analytics.php" class="btn btn-success">Open Analytics</a>
            </div>
            <div class="admin-section">
                <h2>📝 Edit Available Products</h2>
                <p>Manage noodle inventory, add new products, update prices and stock levels</p>
                <a href="products.php" class="btn btn-primary">Manage Products →</a>
            </div>
            <div class="admin-section">
                <h2>📋 User's Booking Details</h2>
                <p>View and update all customer orders, change order status</p>
                <a href="orders.php" class="btn btn-primary">View Orders →</a>
            </div>
            <div class="admin-section">
                <h2>👤 Admin Details</h2>
                <p>Change password or admin profile settings</p>
                <a href="profile.php" class="btn btn-secondary">Edit Profile →</a>
            </div>
            <div class="admin-section">
                <h2>💳 Payment Logs</h2>
                <p>View all transaction records and payment history</p>
                <a href="payments.php" class="btn btn-secondary">View Payments →</a>
            </div>
            <div class="admin-section">
                <h2>👥 User Management</h2>
                <p>Manage registered users, view or delete accounts</p>
                <a href="users.php" class="btn btn-secondary">Manage Users →</a>
            </div>
        </div>
    </div>
    <script src="../assets/app.js"></script>
</body>
</html>
