<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$promotions = getActivePromotions();
$rate = getUsdTwdRate();
$cartBenefits = calculateCartBenefits($_SESSION['cart'] ?? []);
$cartTwd = (int)round($cartBenefits['subtotal'] * $rate);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>活動優惠－無人拉麵商店</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>
    <header>
        <div class="logo"><p>CAMPAIGN ENGINE</p><h1>活動優惠中心</h1></div>
        <nav>
            <a href="index.php">首頁</a>
            <a href="dashboard.php">點餐台</a>
            <a href="rewards.php">點數兌換</a>
            <a href="partners.php">合作店家</a>
            <a href="store-info.php">門市狀態</a>
        </nav>
    </header>

    <section class="feature-hero compact-feature-hero">
        <p class="eyebrow">LIVE PROMOTIONS</p>
        <h2>優惠會在購物車自動套用</h2>
        <p>不用輸入折扣碼，系統會依照商品與消費金額計算目前可使用的活動。</p>
        <a class="btn btn-primary" href="dashboard.php">前往點餐</a>
    </section>

    <section class="feature-card-grid promotion-grid">
        <?php foreach ($promotions as $index => $promotion): ?>
            <?php
            $ends = strtotime($promotion['ends_at']);
            $days = max(0, (int)ceil(($ends - time()) / 86400));
            ?>
            <article class="innovation-card promotion-card" data-promotion-card>
                <span class="card-index">0<?php echo $index + 1; ?></span>
                <span class="status-pill is-live">活動中</span>
                <h2><?php echo htmlspecialchars($promotion['title_zh']); ?></h2>
                <p><?php echo htmlspecialchars($promotion['description']); ?></p>
                <div class="promotion-value">
                    <?php if ($promotion['promotion_type'] === 'percentage'): ?>
                        <?php echo (int)$promotion['discount_value']; ?>% OFF
                    <?php else: ?>
                        約 NT$<?php echo number_format((float)$promotion['discount_value'] * $rate); ?>
                    <?php endif; ?>
                </div>
                <small>
                    <?php if ($days < 3): ?>
                        活動倒數 <strong data-promotion-countdown="<?php echo htmlspecialchars(date(DATE_ATOM, $ends)); ?>"></strong>
                    <?php else: ?>
                        剩餘 <?php echo $days; ?> 天 · 結帳自動套用
                    <?php endif; ?>
                </small>
                <?php if ($promotion['promotion_type'] === 'threshold'): ?>
                    <?php
                    $targetTwd = max(1, (int)round((float)$promotion['threshold_amount'] * $rate));
                    $progress = min(100, (int)round($cartTwd / $targetTwd * 100));
                    ?>
                    <div class="promotion-progress" aria-label="滿額優惠進度">
                        <div><i style="width:<?php echo $progress; ?>%"></i></div>
                        <?php if ($cartTwd >= $targetTwd): ?>
                            <strong>✓ 已達成優惠！</strong>
                        <?php else: ?>
                            <span>目前 NT$<?php echo number_format($cartTwd); ?>，再消費 NT$<?php echo number_format($targetTwd - $cartTwd); ?> 即可享折扣。</span>
                        <?php endif; ?>
                    </div>
                <?php elseif (!empty($promotion['product_code'])): ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="button" class="btn btn-primary btn-small"
                                data-promotion-action
                                data-code="<?php echo htmlspecialchars($promotion['product_code']); ?>"
                                data-quantity="<?php echo $promotion['promotion_type'] === 'product_quantity' ? max(2, (int)$promotion['required_quantity']) : 1; ?>"
                                data-destination="dashboard.php">
                            <?php echo $promotion['promotion_type'] === 'product_quantity' ? '一鍵帶走優惠組' : '直接來一碗'; ?>
                        </button>
                    <?php else: ?>
                        <a class="btn btn-primary btn-small" href="login.php">登入後使用此優惠</a>
                    <?php endif; ?>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
</body>
</html>
