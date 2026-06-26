<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$locations = getDemoLocations();
$hqQuery = '國立高雄大學 高雄市楠梓區大學南路700號';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>門市據點－高雄智慧門市狀態</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>

    <div class="demo-disclosure" role="note">
        <strong data-en="Operations notice" data-zh="營運提醒">Operations notice</strong>
        <span data-en="Store hours, equipment status and service volume are updated through the store operations dashboard."
              data-zh="門市時段、設備狀態與服務量會透過營運系統同步更新。">Store hours, equipment status and service volume are updated through the store operations dashboard.</span>
    </div>

    <section class="feature-hero compact-feature-hero">
        <p class="eyebrow" data-en="KHH STORE NETWORK" data-zh="高雄門市網絡">KHH STORE NETWORK</p>
        <h2 data-en="Store Locations: Kaohsiung Smart Store Status"
            data-zh="門市據點：高雄智慧門市狀態">Store Locations: Kaohsiung Smart Store Status</h2>
        <p data-en="Click a store location to switch the map, inspect unmanned machine health and review the service flow."
           data-zh="點擊門市據點即可切換地圖、查看無人機台健康度，並檢視服務流程。">Click a store location to switch the map, inspect unmanned machine health and review the service flow.</p>
    </section>

    <section class="ops-command-grid" data-location-command>
        <div class="location-grid ops-location-grid">
            <?php foreach ($locations as $index => $location): ?>
                <button type="button"
                        class="innovation-card location-card ops-location-card<?php echo $index === 0 ? ' is-active' : ''; ?>"
                        style="--location-color:<?php echo htmlspecialchars($location['color']); ?>"
                        data-map-query="<?php echo htmlspecialchars($location['map_query']); ?>"
                        data-location-name="<?php echo htmlspecialchars($location['name_zh']); ?>">
                    <span class="location-code"><?php echo htmlspecialchars($location['code']); ?></span>
                    <span class="status-pill is-live"
                          data-en="<?php echo htmlspecialchars($location['hours']); ?> Self-Service"
                          data-zh="<?php echo htmlspecialchars($location['hours']); ?> 全天候自助"><?php echo htmlspecialchars($location['hours']); ?> 全天候自助</span>
                    <small><?php echo htmlspecialchars($location['district']); ?></small>
                    <h2 data-en="<?php echo htmlspecialchars($location['name_en']); ?>"
                        data-zh="<?php echo htmlspecialchars($location['name_zh']); ?>"><?php echo htmlspecialchars($location['name_zh']); ?></h2>
                    <p><?php echo htmlspecialchars($location['address']); ?></p>
                    <div class="service-tag-list">
                        <?php foreach ($location['amenities'] as $amenity): ?><span><?php echo htmlspecialchars($amenity); ?></span><?php endforeach; ?>
                    </div>
                    <div class="ops-metrics">
                        <span><strong><?php echo htmlspecialchars($location['machine_status']); ?></strong><small>機台狀態</small></span>
                        <span><strong><?php echo htmlspecialchars($location['ingredient_status']); ?></strong><small>配料狀態</small></span>
                        <span><strong><?php echo (int)$location['today_orders']; ?></strong><small>今日服務量</small></span>
                        <span><strong><?php echo (int)$location['power_slots']; ?></strong><small>充電座空位</small></span>
                    </div>
                    <small class="sync-note">Last sync: <?php echo htmlspecialchars($location['last_sync']); ?></small>
                </button>
            <?php endforeach; ?>
        </div>

        <aside class="ops-map-panel">
            <div class="ops-map-toolbar">
                <div>
                    <p class="eyebrow">LIVE MAP STATUS</p>
                    <h2 data-selected-location><?php echo htmlspecialchars($locations[0]['name_zh'] ?? '門市據點'); ?></h2>
                    <span data-map-loading>地圖資料已同步。</span>
                </div>
                <button type="button"
                        class="btn btn-secondary btn-small"
                        data-hq-query="<?php echo htmlspecialchars($hqQuery); ?>"
                        data-hq-name="Ramen Core 智慧研發總部｜國立高雄大學">Ramen Core 智慧研發總部</button>
            </div>
            <iframe title="Kaohsiung smart location map"
                    data-location-map
                    src="https://www.google.com/maps?q=<?php echo rawurlencode($locations[0]['map_query'] ?? $hqQuery); ?>&output=embed"
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            <p class="map-disclaimer" data-en="Map data is loaded through a public embedded map view."
               data-zh="地圖資料透過公開嵌入式地圖載入。">地圖資料透過公開嵌入式地圖載入。</p>
        </aside>
    </section>

    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
</body>
</html>
