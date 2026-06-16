<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();
ensureInnovationSchema();

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'The form expired. Please refresh and try again.';
    } elseif (isset($_POST['toggle_promotion'])) {
        $id = (int)$_POST['promotion_id'];
        $stmt = $conn->prepare("UPDATE promotions SET active = NOT active WHERE id = ?");
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? '活動狀態已更新。' : '活動更新失敗。';
    } elseif (isset($_POST['update_reward'])) {
        $id = (int)$_POST['reward_id'];
        $stock = max(0, (int)$_POST['stock']);
        $points = max(1, (int)$_POST['points_required']);
        $stmt = $conn->prepare("UPDATE rewards SET stock = ?, points_required = ? WHERE id = ?");
        $stmt->bind_param('iii', $stock, $points, $id);
        $message = $stmt->execute() ? '獎品設定已更新。' : '獎品更新失敗。';
    } elseif (isset($_POST['toggle_partner'])) {
        $id = (int)$_POST['partner_id'];
        $stmt = $conn->prepare("UPDATE partners SET active = NOT active WHERE id = ?");
        $stmt->bind_param('i', $id);
        $message = $stmt->execute() ? '合作店家狀態已更新。' : '店家更新失敗。';
    } elseif (isset($_POST['update_hours'])) {
        $service = $_POST['service_name'] ?? '';
        $open = $_POST['open_time'] ?? '10:00';
        $close = $_POST['close_time'] ?? '22:00';
        $stmt = $conn->prepare("UPDATE store_hours SET open_time = ?, close_time = ?, is_24_hours = 0 WHERE service_name = ?");
        $stmt->bind_param('sss', $open, $close, $service);
        $message = $stmt->execute() ? '服務時間已更新。' : '時間更新失敗。';
    }
}

$promotions = $conn->query("SELECT * FROM promotions ORDER BY id");
$rewards = $conn->query("SELECT * FROM rewards ORDER BY id");
$partners = $conn->query("SELECT * FROM partners ORDER BY id");
$services = $conn->query("SELECT service_name,service_name_zh,MIN(open_time) open_time,MAX(close_time) close_time,MAX(is_24_hours) is_24_hours FROM store_hours GROUP BY service_name,service_name_zh");
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>活動與會員管理－管理後台</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container admin-container">
    <header>
        <div class="logo"><p>GROWTH CONTROL</p><h1>活動與會員管理</h1></div>
        <nav>
            <a href="index.php">儀表板</a>
            <a href="analytics.php">營運分析</a>
            <a href="products.php">商品</a>
            <a href="orders.php">訂單</a>
            <a href="marketing.php">活動管理</a>
            <a href="../logout.php">登出</a>
        </nav>
    </header>
    <?php if ($message): ?><div class="success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section class="admin-marketing-grid">
        <div class="analytics-panel">
            <div class="panel-heading"><div><p class="eyebrow">PROMOTIONS</p><h2>活動開關</h2></div></div>
            <?php while ($item = $promotions->fetch_assoc()): ?>
                <form method="POST" class="control-row">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <input type="hidden" name="promotion_id" value="<?php echo (int)$item['id']; ?>">
                    <span><strong><?php echo htmlspecialchars($item['title_zh']); ?></strong><small><?php echo date('m/d', strtotime($item['starts_at'])); ?>－<?php echo date('m/d', strtotime($item['ends_at'])); ?></small></span>
                    <button class="btn btn-small <?php echo $item['active'] ? 'btn-success' : 'btn-secondary'; ?>" name="toggle_promotion">
                        <?php echo $item['active'] ? '啟用中' : '已停用'; ?>
                    </button>
                </form>
            <?php endwhile; ?>
        </div>

        <div class="analytics-panel">
            <div class="panel-heading"><div><p class="eyebrow">REWARDS</p><h2>獎品庫存</h2></div></div>
            <?php while ($item = $rewards->fetch_assoc()): ?>
                <form method="POST" class="control-row control-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <input type="hidden" name="reward_id" value="<?php echo (int)$item['id']; ?>">
                    <span><strong><?php echo htmlspecialchars($item['name_zh']); ?></strong></span>
                    <label>點數<input type="number" name="points_required" value="<?php echo (int)$item['points_required']; ?>" min="1"></label>
                    <label>庫存<input type="number" name="stock" value="<?php echo (int)$item['stock']; ?>" min="0"></label>
                    <button class="btn btn-small btn-primary" name="update_reward">儲存</button>
                </form>
            <?php endwhile; ?>
        </div>

        <div class="analytics-panel">
            <div class="panel-heading"><div><p class="eyebrow">PARTNERS</p><h2>合作店家</h2></div></div>
            <?php while ($item = $partners->fetch_assoc()): ?>
                <form method="POST" class="control-row">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <input type="hidden" name="partner_id" value="<?php echo (int)$item['id']; ?>">
                    <span><strong><?php echo htmlspecialchars($item['name']); ?></strong><small><?php echo htmlspecialchars($item['offer_text']); ?></small></span>
                    <button class="btn btn-small <?php echo $item['active'] ? 'btn-success' : 'btn-secondary'; ?>" name="toggle_partner">
                        <?php echo $item['active'] ? '合作中' : '已下架'; ?>
                    </button>
                </form>
            <?php endwhile; ?>
        </div>

        <div class="analytics-panel">
            <div class="panel-heading"><div><p class="eyebrow">STORE HOURS</p><h2>服務時間</h2></div></div>
            <?php while ($item = $services->fetch_assoc()): ?>
                <form method="POST" class="control-row control-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <input type="hidden" name="service_name" value="<?php echo htmlspecialchars($item['service_name']); ?>">
                    <span><strong><?php echo htmlspecialchars($item['service_name_zh']); ?></strong></span>
                    <?php if ($item['is_24_hours']): ?>
                        <span>24 小時</span>
                    <?php else: ?>
                        <input type="time" name="open_time" value="<?php echo substr($item['open_time'], 0, 5); ?>">
                        <input type="time" name="close_time" value="<?php echo substr($item['close_time'], 0, 5); ?>">
                        <button class="btn btn-small btn-primary" name="update_hours">儲存</button>
                    <?php endif; ?>
                </form>
            <?php endwhile; ?>
        </div>
    </section>
</div>
<script src="../assets/app.js"></script>
</body>
</html>
