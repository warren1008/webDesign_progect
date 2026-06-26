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
    } elseif (isset($_POST['add_reward'])) {
        $nameZh = trim($_POST['name_zh'] ?? '');
        $nameEn = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $points = max(1, (int)($_POST['points_required'] ?? 1));
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $type = $_POST['reward_type'] ?? 'coupon';
        $allowedTypes = ['topping', 'coupon', 'limited'];

        if ($nameZh === '' || $nameEn === '' || $description === '' || !in_array($type, $allowedTypes, true)) {
            $error = '請完整填寫獎品名稱、英文名稱、說明與類型。';
        } else {
            $upload = projectUploadImage('new_reward_image', 'assets/images/rewards', $nameEn ?: $nameZh);
            if (!$upload['success']) {
                $error = $upload['message'];
            } else {
                $image = $upload['path'];
                $stmt = $conn->prepare("INSERT INTO rewards (name,name_zh,description,image_path,points_required,stock,reward_type,active) VALUES (?,?,?,?,?,?,?,1)");
                $stmt->bind_param('ssssiis', $nameEn, $nameZh, $description, $image, $points, $stock, $type);
                $message = $stmt->execute() ? '新獎品已新增，前台點數商城會自動顯示。' : '新增獎品失敗。';
            }
        }
    } elseif (isset($_POST['update_reward'])) {
        $id = (int)$_POST['reward_id'];
        $stock = max(0, (int)$_POST['stock']);
        $points = max(1, (int)$_POST['points_required']);
        $currentImage = trim($_POST['current_image'] ?? '');
        $upload = projectUploadImage('reward_image', 'assets/images/rewards', 'reward-' . $id, $currentImage);
        if (!$upload['success']) {
            $error = $upload['message'];
        } else {
            $image = $upload['path'];
            $stmt = $conn->prepare("UPDATE rewards SET stock = ?, points_required = ?, image_path = ? WHERE id = ?");
            $stmt->bind_param('iisi', $stock, $points, $image, $id);
            $message = $stmt->execute() ? '獎品設定已更新。' : '獎品更新失敗。';
        }
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
        <div class="logo">
            <p data-en="GROWTH CONTROL" data-zh="會員營運控制">GROWTH CONTROL</p>
            <h1 data-en="Campaign & Member Management" data-zh="活動與會員管理">活動與會員管理</h1>
        </div>
        <?php include 'includes/admin_nav.php'; ?>
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
            <div class="panel-heading"><div><p class="eyebrow" data-en="REWARDS" data-zh="點數獎品">REWARDS</p><h2 data-en="Reward Inventory" data-zh="獎品庫存">獎品庫存</h2></div></div>
            <form method="POST" class="reward-create-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                <h3 data-en="Add Reward" data-zh="新增點數獎品">新增點數獎品</h3>
                <div class="reward-create-grid">
                    <label><span data-en="Chinese Name" data-zh="中文名稱">中文名稱</span><input type="text" name="name_zh" placeholder="例如：免費溏心蛋" data-placeholder-en="Example: Free Ajitama Egg" data-placeholder-zh="例如：免費溏心蛋" required></label>
                    <label><span data-en="English Name" data-zh="英文名稱">英文名稱</span><input type="text" name="name" placeholder="Free Ajitama Egg" data-placeholder-en="Free Ajitama Egg" data-placeholder-zh="Free Ajitama Egg" required></label>
                    <label><span data-en="Points" data-zh="兌換點數">兌換點數</span><input type="number" name="points_required" value="100" min="1" required></label>
                    <label><span data-en="Stock" data-zh="庫存">庫存</span><input type="number" name="stock" value="20" min="0" required></label>
                    <label><span data-en="Reward Type" data-zh="獎品類型">獎品類型</span>
                        <select name="reward_type">
                            <option value="topping" data-en="Topping" data-zh="加料">加料</option>
                            <option value="coupon" data-en="Coupon" data-zh="優惠券">優惠券</option>
                            <option value="limited" data-en="Limited Item" data-zh="限量商品">限量商品</option>
                        </select>
                    </label>
                    <label><span data-en="Reward Image" data-zh="獎品圖片">獎品圖片</span><input type="file" name="new_reward_image" accept="image/png,image/jpeg,image/webp,image/gif"></label>
                    <label class="reward-create-description"><span data-en="Description" data-zh="獎品說明">獎品說明</span><textarea name="description" rows="3" placeholder="兌換後可於下一碗客製拉麵免費加蛋" data-placeholder-en="Example: Redeem this for a free topping on the next custom ramen." data-placeholder-zh="兌換後可於下一碗客製拉麵免費加蛋" required></textarea></label>
                </div>
                <button class="btn btn-primary btn-small" name="add_reward" data-en="Create Reward" data-zh="新增獎品">新增獎品</button>
            </form>
            <?php while ($item = $rewards->fetch_assoc()): ?>
                <form method="POST" class="control-row control-form reward-admin-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <input type="hidden" name="reward_id" value="<?php echo (int)$item['id']; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($item['image_path'] ?? ''); ?>">
                    <span class="reward-admin-summary">
                        <img class="admin-table-thumb" src="../<?php echo htmlspecialchars(displayImagePath($item['image_path'] ?? '', 'assets/images/rewards/reward-default.svg')); ?>" alt="<?php echo htmlspecialchars($item['name_zh']); ?>">
                        <strong><?php echo htmlspecialchars($item['name_zh']); ?></strong>
                    </span>
                    <label>點數<input type="number" name="points_required" value="<?php echo (int)$item['points_required']; ?>" min="1"></label>
                    <label>庫存<input type="number" name="stock" value="<?php echo (int)$item['stock']; ?>" min="0"></label>
                    <label>獎品圖<input type="file" name="reward_image" accept="image/png,image/jpeg,image/webp,image/gif"></label>
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
    <?php include 'includes/admin_footer.php'; ?>
</div>
<script src="../assets/app.js"></script>
</body>
</html>
