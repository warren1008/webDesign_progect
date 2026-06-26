

<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ensureInnovationSchema();

// AI 修改：資料庫暫時不可用時，首頁仍可用展示資料呈現專題概念
$featured_noodles = [];
if (empty($db_error)) {
    $result = $conn->query("SELECT * FROM noodles WHERE code IN ('N001','N002','N003','N004','N005','N006','N007','N008') ORDER BY code ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $featured_noodles[] = $row;
        }
    }
}

if (empty($featured_noodles)) {
    $featured_noodles = [
        ['code' => 'N001', 'name' => 'Shin Ramyun', 'brand' => 'Nongshim', 'price' => 5.99, 'stock' => 50, 'image' => 'assets/images/noodles/N001-shin-ramyun.webp'],
        ['code' => 'N002', 'name' => 'Indomie Mi Goreng', 'brand' => 'Indomie', 'price' => 3.99, 'stock' => 100, 'image' => 'assets/images/noodles/N002-mi-goreng.webp'],
        ['code' => 'N003', 'name' => 'Mama Tom Yum', 'brand' => 'Mama', 'price' => 2.99, 'stock' => 75, 'image' => 'assets/images/noodles/N003-tom-yum.webp'],
        ['code' => 'N004', 'name' => 'Samyang Buldak', 'brand' => 'Samyang', 'price' => 6.99, 'stock' => 30, 'image' => 'assets/images/noodles/N004-buldak.webp'],
        ['code' => 'N005', 'name' => 'Maruchan Chicken', 'brand' => 'Maruchan', 'price' => 1.99, 'stock' => 200, 'image' => 'assets/images/noodles/N005-chicken.webp'],
        ['code' => 'N006', 'name' => 'Cup Noodles', 'brand' => 'Nissin', 'price' => 2.49, 'stock' => 150, 'image' => 'assets/images/noodles/N006-cup.webp'],
        ['code' => 'N007', 'name' => 'Sapporo Ichiban', 'brand' => 'Sapporo', 'price' => 2.29, 'stock' => 120, 'image' => 'assets/images/noodles/N007-shoyu.webp'],
        ['code' => 'N008', 'name' => 'Nissin Raoh', 'brand' => 'Nissin', 'price' => 3.49, 'stock' => 60, 'image' => 'assets/images/noodles/N008-tonkotsu.webp'],
    ];
}

// AI 修改：為熱門商品配置一致風格圖片與推薦屬性
$noodle_visuals = [
    'N001' => ['image' => 'N001-shin-ramyun.webp', 'spice' => 'hot', 'style' => 'spicy', 'mood' => 'energy'],
    'N002' => ['image' => 'N002-mi-goreng.webp', 'spice' => 'medium', 'style' => 'dry', 'mood' => 'energy'],
    'N003' => ['image' => 'N003-tom-yum.webp', 'spice' => 'hot', 'style' => 'sour', 'mood' => 'adventure'],
    'N004' => ['image' => 'N004-buldak.webp', 'spice' => 'hot', 'style' => 'dry', 'mood' => 'adventure'],
    'N005' => ['image' => 'N005-chicken.webp', 'spice' => 'mild', 'style' => 'classic', 'mood' => 'comfort'],
    'N006' => ['image' => 'N006-cup.webp', 'spice' => 'mild', 'style' => 'light', 'mood' => 'quick'],
    'N007' => ['image' => 'N007-shoyu.webp', 'spice' => 'mild', 'style' => 'soy', 'mood' => 'comfort'],
    'N008' => ['image' => 'N008-tonkotsu.webp', 'spice' => 'medium', 'style' => 'rich', 'mood' => 'reward'],
];
$demo_locations = array_slice(getDemoLocations(), 0, 3);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staffless Instant Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>

        <aside class="safety-banner" data-safety-banner role="status">
            <span aria-hidden="true">!</span>
            <p data-en="Anti-fraud notice: Staffless Ramen will never ask you to operate an ATM, transfer money, exchange foreign currency or cancel a mistaken subscription."
               data-zh="反詐騙提醒：Staffless Ramen 不會要求操作 ATM、轉帳、兌換外幣或解除錯誤設定；如遇可疑情況可撥打 165。">
                Anti-fraud notice: Staffless Ramen will never ask you to operate an ATM, transfer money, exchange foreign currency or cancel a mistaken subscription.
            </p>
            <button type="button" data-safety-pause aria-label="Pause">Ⅱ</button>
            <button type="button" data-safety-close aria-label="Close">&times;</button>
        </aside>
        
        <div class="hero">
            <div class="hero-content">
                <h2>Welcome to the Future of Noodle Shopping</h2>
                <p class="hero-subtitle">A 24/7 staffless instant noodle store where customers scan shelf codes, pay online, and pick up with a secure code.</p>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <p>Grab your favorite instant noodles from our shelves</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <p>Enter the noodle code and quantity on our website</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <p>Pay online with your credit/debit card</p>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <p>Show your pickup code and enjoy your noodles!</p>
                    </div>
                </div>
                <div class="auth-buttons">
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php?next=dashboard.php'; ?>" class="btn btn-primary">Start Shopping →</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="customize.php" class="btn btn-success">Build Custom Ramen</a>
                    <?php endif; ?>
                    <a href="#kiosk-demo" class="btn btn-secondary">View Kiosk Flow</a>
                </div>
            </div>
        </div>

        <section class="experience-section" id="how-it-works">
            <!-- AI 修改：補齊首頁對無人拉麵商店核心流程的展示 -->
            <div class="section-heading">
                <p>STAFFLESS FLOW</p>
                <h2>From shelf to pickup code in four steps</h2>
            </div>
            <div class="timeline">
                <article class="timeline-item flip-card" tabindex="0" data-flip-card>
                    <div class="flip-card-inner">
                        <div class="flip-card-face flip-card-front">
                            <span class="flip-card-icon" aria-hidden="true">QR</span>
                            <span>01</span>
                            <h3 data-en="Scan shelf QR Code" data-zh="掃描貨架 QR Code">Scan shelf QR Code</h3>
                        </div>
                        <div class="flip-card-face flip-card-back">
                            <strong data-en="Pick it up, scan it, know it." data-zh="拿起來、掃一下，就知道是哪一款。">Pick it up, scan it, know it.</strong>
                            <p data-en="Each noodle slot has a product code and QR label, so customers can identify the item on their own without waiting for staff."
                               data-zh="每個泡麵貨位都有商品代碼與 QR 標籤，顧客不需要店員協助，也能自己確認商品。">Each noodle slot has a product code and QR label, so customers can identify the item on their own without waiting for staff.</p>
                        </div>
                    </div>
                </article>
                <article class="timeline-item flip-card" tabindex="0" data-flip-card>
                    <div class="flip-card-inner">
                        <div class="flip-card-face flip-card-front">
                            <span class="flip-card-icon" aria-hidden="true">#</span>
                            <span>02</span>
                            <h3 data-en="Enter code to add" data-zh="輸入代碼加入">Enter code to add</h3>
                        </div>
                        <div class="flip-card-face flip-card-back">
                            <strong data-en="The cart checks before it accepts." data-zh="加入前，系統會先幫你確認。">The cart checks before it accepts.</strong>
                            <p data-en="The website checks the noodle code, quantity, price and stock, then sends the selected item into the cart."
                               data-zh="網站會先檢查商品代碼、數量、價格與庫存，再把選好的泡麵加入購物車。">The website checks the noodle code, quantity, price and stock, then sends the selected item into the cart.</p>
                        </div>
                    </div>
                </article>
                <article class="timeline-item flip-card" tabindex="0" data-flip-card>
                    <div class="flip-card-inner">
                        <div class="flip-card-face flip-card-front">
                            <span class="flip-card-icon" aria-hidden="true">$</span>
                            <span>03</span>
                            <h3 data-en="Pay online" data-zh="線上付款">Pay online</h3>
                        </div>
                        <div class="flip-card-face flip-card-back">
                            <strong data-en="Payment happens in the same flow." data-zh="付款和點餐接在同一條流程裡。">Payment happens in the same flow.</strong>
                            <p data-en="Checkout verifies payment details and stores the transaction result, so the order status can continue into pickup and admin management."
                               data-zh="結帳會驗證付款資料並留下交易狀態，後續才能接到取餐與後台管理。">Checkout verifies payment details and stores the transaction result, so the order status can continue into pickup and admin management.</p>
                        </div>
                    </div>
                </article>
                <article class="timeline-item flip-card" tabindex="0" data-flip-card>
                    <div class="flip-card-inner">
                        <div class="flip-card-face flip-card-front">
                            <span class="flip-card-icon" aria-hidden="true">PIN</span>
                            <span>04</span>
                            <h3 data-en="Unlock pickup" data-zh="解鎖取餐">Unlock pickup</h3>
                        </div>
                        <div class="flip-card-face flip-card-back">
                            <strong data-en="The pickup code becomes the counter." data-zh="取餐碼就是你的無人櫃台。">The pickup code becomes the counter.</strong>
                            <p data-en="After payment, the order receives a pickup code for the unmanned pickup counter and completes the self-service journey."
                               data-zh="付款完成後會產生專屬取餐碼，用來解鎖無人取餐櫃並完成自助取餐。">After payment, the order receives a pickup code for the unmanned pickup counter and completes the self-service journey.</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="kiosk-demo-section" id="kiosk-demo">
            <div class="kiosk-copy">
                <p class="eyebrow">SELF-SERVICE FLOW</p>
                <h2>Self-service kiosk preview</h2>
                <p>Choose a noodle below and watch the kiosk preview update. The same selection flow continues through login, cart and checkout.</p>
                <div class="demo-controls" data-demo-controls>
                    <button type="button" class="btn btn-primary" data-demo-choice="N001|Shin Ramyun|Spicy Korean classic">Scan N001</button>
                    <button type="button" class="btn btn-secondary" data-demo-choice="N004|Samyang Buldak|Extra spicy fire noodle">Scan N004</button>
                    <button type="button" class="btn btn-success" data-demo-choice="N008|Nissin Raoh|Premium tonkotsu">Scan N008</button>
                </div>
            </div>
            <div class="kiosk-device" data-kiosk-demo>
                <div class="kiosk-device-top">
                    <span class="kiosk-dot"></span>
                    <strong>RAMEN OS</strong>
                    <small>ONLINE</small>
                </div>
                <div class="kiosk-device-screen">
                    <p class="kiosk-status">Waiting for QR scan...</p>
                    <h3 data-demo-name>No noodle selected</h3>
                    <p data-demo-desc>Tap a scan button to load a shelf item.</p>
                    <div class="kiosk-code" data-demo-code>----</div>
                </div>
                <div class="kiosk-slots">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </section>

        <section class="ai-recommend-section" id="ai-recommend">
            <!-- AI 修改：以口味偏好即時配對商品，作為期末展示的 AI 推薦功能 -->
            <div class="section-heading">
                <p>AI TASTE MATCH</p>
                <h2>Find your ramen personality</h2>
                <span class="section-description">Choose three preferences and let the recommendation engine find your best match.</span>
            </div>
            <div class="ai-recommend-layout">
                <form class="ai-preference-panel" data-ai-recommender>
                    <div class="preference-field">
                        <label for="taste-preference">🥣 Flavor style</label>
                        <select id="taste-preference" name="taste">
                            <option value="rich">Rich and creamy</option>
                            <option value="classic">Classic and balanced</option>
                            <option value="sour">Sour and aromatic</option>
                            <option value="dry">Dry noodle texture</option>
                        </select>
                    </div>
                    <div class="preference-field">
                        <label for="spice-preference">🌶️ Spice level</label>
                        <select id="spice-preference" name="spice">
                            <option value="mild">Mild</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hot">Hot</option>
                        </select>
                    </div>
                    <div class="preference-field">
                        <label for="mood-preference">⚡ Current mood</label>
                        <select id="mood-preference" name="mood">
                            <option value="comfort">Need comfort</option>
                            <option value="quick">Need a quick meal</option>
                            <option value="energy">Need energy</option>
                            <option value="adventure">Want an adventure</option>
                            <option value="reward" selected>Treat myself</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary ai-match-button">✨ Generate My Match</button>
                    <p class="ai-disclaimer">Taste recommendations do not upload personal data.</p>
                </form>
                <div class="ai-result-panel" data-ai-result aria-live="polite">
                    <div class="ai-orbit" aria-hidden="true"><span></span><span></span><span></span></div>
                    <p class="ai-result-status">AI advisor is ready</p>
                    <img src="assets/images/noodles/N008-tonkotsu.webp" alt="Recommended ramen preview" data-ai-result-image>
                    <div class="ai-result-copy">
                        <span class="ai-confidence" data-ai-confidence>96% MATCH</span>
                        <h3 data-ai-result-name>Nissin Raoh</h3>
                        <p data-ai-result-reason>Rich tonkotsu broth is a rewarding match for a premium ramen moment.</p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php?code=N008' : 'login.php?next=dashboard.php%3Fcode%3DN008'; ?>"
                           class="btn btn-success"
                           data-ai-order-link
                           data-authenticated="<?php echo isset($_SESSION['user_id']) ? '1' : '0'; ?>">🛒 Order Recommendation</a>
                    </div>
                </div>
            </div>
        </section>
        
        <div class="featured-noodles" id="menu">
            <h2>Popular Instant Noodles</h2>
            <?php if (!empty($db_error)): ?>
                <div class="info">A temporary menu is showing because the product database is not reachable.</div>
            <?php endif; ?>
            <div class="noodle-grid">
                <?php foreach ($featured_noodles as $noodle):
                    $visual = $noodle_visuals[$noodle['code']] ?? $noodle_visuals['N005'];
                    $noodleImage = displayImagePath($noodle['image'] ?? '', 'assets/images/noodles/' . $visual['image']);
                ?>
                <div class="noodle-card" data-noodle-product
                     data-code="<?php echo htmlspecialchars($noodle['code']); ?>"
                     data-spice="<?php echo htmlspecialchars($visual['spice']); ?>"
                     data-style="<?php echo htmlspecialchars($visual['style']); ?>"
                     data-mood="<?php echo htmlspecialchars($visual['mood']); ?>">
                    <div class="noodle-image-wrap">
                        <img src="<?php echo htmlspecialchars($noodleImage); ?>"
                             alt="<?php echo htmlspecialchars($noodle['name']); ?>"
                             class="noodle-image" loading="lazy">
                        <span class="product-availability">● LIVE STOCK</span>
                    </div>
                    <h3><?php echo htmlspecialchars($noodle['name']); ?></h3>
                    <p class="brand"><?php echo htmlspecialchars($noodle['brand']); ?></p>
                    <p class="code">Code: <strong><?php echo htmlspecialchars($noodle['code']); ?></strong></p>
                    <p class="price">$<?php echo number_format($noodle['price'], 2); ?></p>
                    <p class="stock">In Stock: <?php echo $noodle['stock']; ?></p>
                    <button type="button" class="btn btn-secondary btn-small" data-demo-choice="<?php echo htmlspecialchars($noodle['code'] . '|' . $noodle['name'] . '|' . $noodle['brand']); ?>">Load Code</button>
                    <?php
                        $order_target = 'dashboard.php?code=' . rawurlencode($noodle['code']);
                        $order_link = isset($_SESSION['user_id'])
                            ? $order_target
                            : 'login.php?next=' . rawurlencode($order_target);
                    ?>
                    <a href="<?php echo htmlspecialchars($order_link); ?>" class="btn btn-primary btn-small">Order This</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <section class="home-location-preview">
            <div class="section-heading">
                <p>SMART LOCATION NETWORK</p>
                <h2 data-en="Explore our smart-store network"
                    data-zh="探索智慧連鎖據點">Explore our smart-store network</h2>
                <span class="section-description" data-en="Each location is designed for self-service ordering, pickup and store monitoring."
                      data-zh="每個據點皆支援自助點餐、取餐與門市狀態監控。">Each location is designed for self-service ordering, pickup and store monitoring.</span>
            </div>
            <div class="location-preview-grid">
                <?php foreach ($demo_locations as $location): ?>
                    <article class="innovation-card location-card" style="--location-color:<?php echo htmlspecialchars($location['color']); ?>">
                        <span class="location-code"><?php echo htmlspecialchars($location['code']); ?></span>
                        <h3 data-en="<?php echo htmlspecialchars($location['name_en']); ?>"
                            data-zh="<?php echo htmlspecialchars($location['name_zh']); ?>"><?php echo htmlspecialchars($location['name_en']); ?></h3>
                        <p><?php echo htmlspecialchars($location['district']); ?> · <?php echo htmlspecialchars($location['hours']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="btn btn-primary" href="locations.php"
               data-en="View Store Locations" data-zh="查看門市據點">View Store Locations</a>
        </section>

        <section class="brand-story-section" id="brand-story">
            <div class="section-heading">
                <p data-en="BRAND STORY" data-zh="品牌故事">BRAND STORY</p>
                <h2 data-en="A smarter unmanned ramen store"
                    data-zh="更智慧的無人拉麵門市">A smarter unmanned ramen store</h2>
                <span class="section-description"
                      data-en="Staffless Ramen combines instant-noodle retail, digital payment, smart pickup and member rewards into one continuous experience."
                      data-zh="Staffless Ramen 將泡麵零售、線上付款、智慧取餐與會員回饋整合成一條完整體驗。">
                    Staffless Ramen combines instant-noodle retail, digital payment, smart pickup and member rewards into one continuous experience.
                </span>
            </div>
            <div class="brand-story-grid">
                <article class="innovation-card flip-card" tabindex="0" data-flip-card>
                    <div class="flip-card-inner">
                        <div class="flip-card-face flip-card-front">
                            <span class="status-pill is-live" data-en="Mission" data-zh="理念">Mission</span>
                            <span class="flip-card-icon" aria-hidden="true">24H</span>
                            <h3 data-en="Fast, transparent and always available" data-zh="快速、透明、隨時可用">Fast, transparent and always available</h3>
                        </div>
                        <div class="flip-card-face flip-card-back">
                            <strong data-en="Customers stay in control from start to finish." data-zh="從選購到取餐，都讓顧客自己掌握。">Customers stay in control from start to finish.</strong>
                            <p data-en="Customers can browse products, review promotions, add toppings, pay online and receive a pickup code without waiting for counter service."
                               data-zh="顧客可自行瀏覽商品、確認活動、加選配料、線上付款並取得取餐碼，不必等待櫃台服務。">Customers can browse products, review promotions, add toppings, pay online and receive a pickup code without waiting for counter service.</p>
                        </div>
                    </div>
                </article>
                <article class="innovation-card flip-card" tabindex="0" data-flip-card>
                    <div class="flip-card-inner">
                        <div class="flip-card-face flip-card-front">
                            <span class="status-pill is-live" data-en="Operations" data-zh="營運">Operations</span>
                            <span class="flip-card-icon" aria-hidden="true">OPS</span>
                            <h3 data-en="Operational details that keep service moving" data-zh="讓服務持續運轉的營業細節">Operational details that keep service moving</h3>
                        </div>
                        <div class="flip-card-face flip-card-back">
                            <strong data-en="Small details make the store feel alive." data-zh="細節越完整，網站越像真的在營運。">Small details make the store feel alive.</strong>
                            <p data-en="The website shows stock, checkout, member points, receipts, store hours and service monitoring so daily operations stay clear."
                               data-zh="網站呈現庫存、結帳、會員點數、收據、營業時間與服務監控，讓日常營運狀態更清楚。">The website shows stock, checkout, member points, receipts, store hours and service monitoring so daily operations stay clear.</p>
                        </div>
                    </div>
                </article>
                <article class="innovation-card flip-card" tabindex="0" data-flip-card>
                    <div class="flip-card-inner">
                        <div class="flip-card-face flip-card-front">
                            <span class="status-pill is-live" data-en="Trust" data-zh="信任">Trust</span>
                            <span class="flip-card-icon" aria-hidden="true">SAFE</span>
                            <h3 data-en="Clear service guidance" data-zh="清楚的服務指引">Clear service guidance</h3>
                        </div>
                        <div class="flip-card-face flip-card-back">
                            <strong data-en="Every step tells customers what happens next." data-zh="每個步驟都讓顧客知道下一步。">Every step tells customers what happens next.</strong>
                            <p data-en="Payment, pickup and support flows follow a consistent customer journey, from shelf selection to post-order service."
                               data-zh="付款、取餐與客服流程依照一致的顧客旅程設計，從貨架選購一路延伸到售後服務。">Payment, pickup and support flows follow a consistent customer journey, from shelf selection to post-order service.</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
