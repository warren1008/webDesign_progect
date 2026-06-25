<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_reward'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'The form expired. Please refresh and try again.';
    } else {
        $result = redeemReward((int)$_SESSION['user_id'], (int)($_POST['reward_id'] ?? 0));
        if ($result['success']) {
            $message = '兌換成功！兌換碼：' . $result['code'];
        } else {
            $error = $result['message'];
        }
    }
}
$rewards = getRewards();
$balance = getUserPointBalance((int)$_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>點數兌換－無人拉麵商店</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>
    <header>
        <div class="logo"><p data-en="MEMBER REWARDS" data-zh="會員專屬回饋">MEMBER REWARDS</p><h1>點數兌換商城</h1></div>
        <nav>
            <a href="dashboard.php">點餐台</a>
            <a href="member-center.php">會員中心</a>
            <a href="promotions.php">活動優惠</a>
            <a href="partners.php">合作店家</a>
            <a href="logout.php">登出</a>
        </nav>
    </header>
    <?php if ($message): ?><div class="success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section class="points-banner">
        <div><span>目前可用點數</span><strong data-reward-balance><?php echo $balance; ?> P</strong></div>
        <p>每消費 NT$10 累積 1 點，10 點可折抵 NT$1。</p>
    </section>

    <section class="feature-card-grid reward-grid">
        <?php foreach ($rewards as $reward): ?>
            <article class="innovation-card reward-card">
                <span class="reward-icon"><?php echo $reward['reward_type'] === 'limited' ? '◆' : ($reward['reward_type'] === 'topping' ? '＋' : '券'); ?></span>
                <span class="status-pill <?php echo (int)$reward['stock'] < 15 ? 'is-warning low-stock-pulse' : 'is-live'; ?>"
                      data-reward-stock="<?php echo (int)$reward['stock']; ?>">
                    <?php echo (int)$reward['stock'] < 15 ? '🔥 賸餘' : '餘額'; ?> <?php echo (int)$reward['stock']; ?>
                </span>
                <h2><?php echo htmlspecialchars($reward['name_zh']); ?></h2>
                <p><?php echo htmlspecialchars($reward['description']); ?></p>
                <strong class="points-price"><?php echo (int)$reward['points_required']; ?> 點</strong>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <input type="hidden" name="reward_id" value="<?php echo (int)$reward['id']; ?>">
                    <button type="button" class="btn btn-primary" name="redeem_reward"
                        data-redeem-reward="<?php echo (int)$reward['id']; ?>"
                        <?php echo $balance < (int)$reward['points_required'] || (int)$reward['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <?php echo $balance < (int)$reward['points_required'] ? '點數不足' : '立即兌換'; ?>
                    </button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
</body>
</html>
