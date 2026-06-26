<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

$summary = $conn->query("
    SELECT COUNT(*) AS orders_count,
           COALESCE(SUM(total_amount), 0) AS revenue,
           COALESCE(AVG(total_amount), 0) AS average_order
    FROM orders WHERE payment_status = 'paid'
")->fetch_assoc();

$popular = $conn->query("
    SELECT n.code, n.name, n.stock,
           COALESCE(SUM(oi.quantity), 0) AS sold,
           COALESCE(SUM(oi.quantity * oi.price), 0) AS revenue
    FROM noodles n
    LEFT JOIN order_items oi ON oi.noodle_id = n.id
    GROUP BY n.id
    ORDER BY sold DESC, n.code
");

$statusRows = $conn->query("
    SELECT order_status, COUNT(*) AS total
    FROM orders GROUP BY order_status ORDER BY total DESC
");
$statuses = [];
while ($row = $statusRows->fetch_assoc()) {
    $statuses[$row['order_status']] = (int)$row['total'];
}
$maxStatus = max(1, ...array_values($statuses ?: ['none' => 1]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Analytics - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
<div class="container admin-container">
    <header>
        <div class="logo">
            <p class="eyebrow">STORE INTELLIGENCE</p>
            <h1>Smart Analytics</h1>
        </div>
        <?php include 'includes/admin_nav.php'; ?>
    </header>

    <section class="analytics-summary">
        <div><span>Paid Orders</span><strong><?php echo (int)$summary['orders_count']; ?></strong></div>
        <div><span>Revenue</span><strong>$<?php echo number_format($summary['revenue'], 2); ?></strong></div>
        <div><span>Average Order</span><strong>$<?php echo number_format($summary['average_order'], 2); ?></strong></div>
        <div><span>Automation Score</span><strong><?php echo $summary['orders_count'] ? '96%' : '--'; ?></strong></div>
    </section>

    <div class="analytics-grid">
        <section class="analytics-panel">
            <div class="panel-heading">
                <div><p class="eyebrow">DEMAND SIGNAL</p><h2>Product Performance</h2></div>
                <span>Live database</span>
            </div>
            <div class="product-performance-list">
                <?php while ($item = $popular->fetch_assoc()): 
                    $risk = (int)$item['stock'] < 10;
                    $suggested = max(0, (int)$item['sold'] * 2 - (int)$item['stock']);
                ?>
                    <div class="performance-row">
                        <div class="performance-name">
                            <strong><?php echo htmlspecialchars($item['code']); ?> · <?php echo htmlspecialchars($item['name']); ?></strong>
                            <small><?php echo (int)$item['sold']; ?> sold · $<?php echo number_format($item['revenue'], 2); ?> revenue</small>
                        </div>
                        <div class="stock-signal <?php echo $risk ? 'is-risk' : ''; ?>">
                            <span><?php echo (int)$item['stock']; ?> in stock</span>
                            <strong><?php echo $suggested > 0 ? "Restock +{$suggested}" : 'Stock healthy'; ?></strong>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section class="analytics-panel">
            <div class="panel-heading">
                <div><p class="eyebrow">ORDER PIPELINE</p><h2>Status Distribution</h2></div>
            </div>
            <div class="status-chart">
                <?php foreach ($statuses as $status => $total): ?>
                    <div class="status-chart-row">
                        <span><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                        <div><i style="width: <?php echo round($total / $maxStatus * 100); ?>%"></i></div>
                        <strong><?php echo $total; ?></strong>
                    </div>
                <?php endforeach; ?>
                <?php if (!$statuses): ?><p>No order data yet.</p><?php endif; ?>
            </div>
            <div class="analytics-insight">
                <span>AI NOTE</span>
                <p>
                    <?php echo $summary['orders_count']
                        ? 'Prioritize products with strong sales and less than two demand cycles of stock.'
                        : 'Complete a few orders to activate sales and inventory insights.'; ?>
                </p>
            </div>
        </section>
    </div>
    <?php include 'includes/admin_footer.php'; ?>
</div>
<script src="../assets/app.js"></script>
</body>
</html>
