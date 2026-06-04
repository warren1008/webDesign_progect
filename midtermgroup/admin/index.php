<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

// Get stats
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$total_noodles = $conn->query("SELECT COUNT(*) as count FROM noodles")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM noodles WHERE stock < 10")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Noodle Store</title>
    <link rel="stylesheet" href="../assets/style.css">
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
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="payments.php">Payments</a>
                <a href="profile.php">Profile</a>
                <a href="../logout.php">Logout</a>
            </div>
        </header>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $total_orders; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Total Sales</h3>
                    <p class="stat-number">$<?php echo number_format($total_sales ?? 0, 2); ?></p>
                </div>
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
</body>
</html>