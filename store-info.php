<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$services = getTodayStoreHours();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>門市狀態－無人拉麵商店</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>
    <header>
        <div class="logo"><p data-en="LIVE STORE SYSTEM" data-zh="智慧店態即時監控">LIVE STORE SYSTEM</p><h1>營業時間與設備狀態</h1></div>
        <nav>
            <a href="index.php">首頁</a>
            <a href="dashboard.php">點餐台</a>
            <a href="partners.php">合作店家</a>
            <a href="promotions.php">活動優惠</a>
            <a href="locations.php">門市據點</a>
        </nav>
    </header>

    <section class="store-status-hero">
        <div class="store-pulse"><span></span><strong data-en="ONLINE" data-zh="全系統上線">ONLINE</strong></div>
        <div>
            <p class="eyebrow">TODAY · <?php echo date('Y/m/d'); ?></p>
            <h2>無人商店目前正常營業</h2>
            <p>商店購物區全天開放；自動廚房與取餐櫃依服務時段運作。</p>
        </div>
        <div class="live-clock" data-live-clock><?php echo date('H:i:s'); ?></div>
    </section>

    <section class="service-status-grid">
        <?php foreach ($services as $service): ?>
            <?php $status = serviceStatus($service['open_time'], $service['close_time'], (bool)$service['is_24_hours'], (bool)$service['is_closed']); ?>
            <article class="service-status-card">
                <span class="status-pill is-<?php echo $status['key']; ?>"><?php echo $status['label']; ?></span>
                <h2><?php echo htmlspecialchars($service['service_name_zh']); ?></h2>
                <strong>
                    <?php echo $service['is_24_hours'] ? '00:00－24:00' : substr($service['open_time'], 0, 5) . '－' . substr($service['close_time'], 0, 5); ?>
                </strong>
                <p><?php echo $service['service_name'] === 'Automated Kitchen' ? '客製拉麵與加熱服務' : ($service['service_name'] === 'Pickup Locker' ? '取餐碼解鎖與保溫服務' : '貨架選購與線上結帳'); ?></p>
                <?php if ($service['service_name'] === 'Automated Kitchen'): ?>
                    <div class="iot-metrics">
                        <span>機台熱水溫度：<strong>98°C</strong></span>
                        <div><i style="width:98%"></i></div>
                        <span>智能製麵碗庫存：<strong>良好 75%</strong></span>
                        <div><i style="width:75%"></i></div>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="maintenance-notice">
        <span>系統公告</span>
        <h2>每日 03:30－03:45 進行貨架盤點</h2>
        <p>盤點期間可瀏覽商品與會員中心，部分低庫存商品可能暫停加入購物車。</p>
    </section>
    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
<script>
setInterval(() => {
    const clock = document.querySelector('[data-live-clock]');
    if (clock) clock.textContent = new Date().toLocaleTimeString('zh-TW', { hour12: false });
}, 1000);
</script>
</body>
</html>
