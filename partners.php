<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$partners = getPartners();
$partnerImages = [
    'NEON TEA' => 'assets/images/partners/partner-neon-tea.png',
    'BYTE MART' => 'assets/images/partners/partner-byte-mart.png',
    'MOCHI LAB' => 'assets/images/partners/partner-mochi-lab.png',
];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>合作店家－無人拉麵商店</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>
    <header>
        <div class="logo"><p>CONNECTED DISTRICT</p><h1>合作店家</h1></div>
        <nav>
            <a href="index.php">首頁</a>
            <a href="promotions.php">活動優惠</a>
            <a href="store-info.php">門市狀態</a>
            <a href="<?php echo isset($_SESSION['user_id']) ? 'rewards.php' : 'login.php'; ?>">點數兌換</a>
        </nav>
    </header>

    <section class="partner-map">
        <div class="map-grid" aria-label="合作商圈地圖">
            <span class="map-node store-node">RAMEN<br>CORE</span>
            <i class="network-line line-1"></i><i class="network-line line-2"></i><i class="network-line line-3"></i>
            <?php foreach ($partners as $index => $partner): ?>
                <button type="button" class="map-node partner-node node-<?php echo $index + 1; ?>"
                      data-partner-target="partner-<?php echo (int)$partner['id']; ?>"
                      style="--partner-color:<?php echo htmlspecialchars($partner['color']); ?>">
                    <?php echo htmlspecialchars($partner['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div>
            <p class="eyebrow">SMART PARTNER NETWORK</p>
            <h2>取餐碼也是合作優惠通行證</h2>
            <p>完成拉麵訂單後，可在合作品牌出示取餐碼享用跨店優惠。</p>
        </div>
    </section>

    <section class="feature-card-grid partner-grid">
        <?php foreach ($partners as $partner): ?>
            <?php
                $status = serviceStatus($partner['open_time'], $partner['close_time']);
                $partnerImage = displayImagePath($partnerImages[$partner['name']] ?? '', 'assets/images/rewards/reward-default.svg');
            ?>
            <article class="innovation-card partner-card flip-card" tabindex="0" data-flip-card id="partner-<?php echo (int)$partner['id']; ?>"
                     style="--partner-color:<?php echo htmlspecialchars($partner['color']); ?>">
                <div class="flip-card-inner">
                    <div class="flip-card-face flip-card-front partner-card-front">
                        <figure class="partner-image-frame">
                            <img src="<?php echo htmlspecialchars($partnerImage); ?>" alt="<?php echo htmlspecialchars($partner['name']); ?>" loading="lazy">
                        </figure>
                        <span class="status-pill is-<?php echo $status['key']; ?>"><?php echo $status['label']; ?></span>
                        <small><?php echo htmlspecialchars($partner['category']); ?></small>
                        <h2><?php echo htmlspecialchars($partner['name']); ?></h2>
                        <p><?php echo htmlspecialchars($partner['description']); ?></p>
                    </div>
                    <div class="flip-card-face flip-card-back partner-card-back">
                        <small data-en="Pickup Code Perk" data-zh="取餐碼合作優惠">取餐碼合作優惠</small>
                        <h2><?php echo htmlspecialchars($partner['name']); ?></h2>
                        <div class="partner-offer"><?php echo htmlspecialchars($partner['offer_text']); ?></div>
                        <time><?php echo substr($partner['open_time'], 0, 5); ?>－<?php echo substr($partner['close_time'], 0, 5); ?></time>
                        <p data-en="Show your pickup code after checkout to redeem this partner offer."
                           data-zh="完成訂單後出示取餐碼，即可在合作店家使用專屬優惠。">完成訂單後出示取餐碼，即可在合作店家使用專屬優惠。</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button type="button" class="btn btn-secondary btn-small" data-unlock-partner data-en="Verify Pickup Code" data-zh="驗證拉麵通行證">驗證拉麵通行證</button>
                        <?php else: ?>
                            <a class="btn btn-secondary btn-small" href="login.php" data-en="Log in to Unlock Offer" data-zh="登入後解鎖合作優惠">登入後解鎖合作優惠</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
</body>
</html>
