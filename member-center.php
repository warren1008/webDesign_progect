<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
ensureInnovationSchema();
$balance = getUserPointBalance((int)$_SESSION['user_id']);
$transactions = $conn->query("SELECT * FROM point_transactions WHERE user_id = " . (int)$_SESSION['user_id'] . " ORDER BY id DESC LIMIT 8");
$redemptions = $conn->query("SELECT rr.*,r.name_zh FROM reward_redemptions rr
    JOIN rewards r ON r.id=rr.reward_id WHERE rr.user_id=" . (int)$_SESSION['user_id'] . " ORDER BY rr.id DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員中心－無人拉麵商店</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>
    <header>
        <div class="logo"><p>MEMBER ID · <?php echo (int)$_SESSION['user_id']; ?></p><h1>會員點數中心</h1></div>
        <nav>
            <a href="dashboard.php">點餐台</a>
            <a href="rewards.php">點數商城</a>
            <a href="promotions.php">活動優惠</a>
            <a href="order-history.php">訂單</a>
            <a href="profile.php">個人資料</a>
        </nav>
    </header>

    <section class="member-dashboard">
        <div class="member-level-card">
            <span data-en="NEON MEMBER" data-zh="霓虹等級會員">NEON MEMBER</span>
            <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <strong data-points-count="<?php echo $balance; ?>"><?php echo $balance; ?> P</strong>
            <div class="level-meter"><i style="width:<?php echo min(100, ($balance % 500) / 5); ?>%"></i></div>
            <small>距離下一等級還差 <?php echo 500 - ($balance % 500); ?> 點</small>
        </div>
        <div class="member-rules">
            <h2>點數規則</h2>
            <p>每消費 NT$10 累積 1 點</p>
            <p>10 點可於結帳折抵 NT$1</p>
            <p>單筆訂單最多折抵商品金額 50%</p>
            <a class="btn btn-primary" href="rewards.php">前往兌換商城</a>
        </div>
    </section>

    <div class="member-history-grid">
        <section class="history-panel">
            <h2>點數紀錄</h2>
            <?php if ($transactions && $transactions->num_rows): while ($item = $transactions->fetch_assoc()): ?>
                <div class="history-row">
                    <span><?php echo htmlspecialchars($item['description']); ?><small><?php echo date('Y/m/d H:i', strtotime($item['created_at'])); ?></small></span>
                    <strong class="<?php echo (int)$item['points'] >= 0 ? 'positive' : 'negative'; ?>"><?php echo (int)$item['points'] >= 0 ? '+' : ''; ?><?php echo (int)$item['points']; ?> 點</strong>
                </div>
            <?php endwhile; else: ?><p>完成第一筆訂單後，點數紀錄會出現在這裡。</p><?php endif; ?>
        </section>
        <section class="history-panel">
            <h2>我的兌換券</h2>
            <?php if ($redemptions && $redemptions->num_rows): while ($item = $redemptions->fetch_assoc()): ?>
                <div class="history-row">
                    <span><?php echo htmlspecialchars($item['name_zh']); ?><small><?php echo htmlspecialchars($item['redemption_code']); ?></small></span>
                    <strong class="<?php echo $item['status'] === 'available' ? 'positive' : ''; ?>">
                        <?php echo $item['status'] === 'available' ? '可使用' : ($item['status'] === 'used' ? '已使用' : '已過期'); ?>
                    </strong>
                    <?php if ($item['status'] === 'available'): ?>
                        <button type="button" class="btn btn-secondary btn-small"
                                data-voucher-code="<?php echo htmlspecialchars($item['redemption_code']); ?>">出示條碼</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; else: ?><p>尚未兌換獎品。</p><?php endif; ?>
        </section>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
</body>
</html>
