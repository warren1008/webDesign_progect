

<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// AI 修改：資料庫暫時不可用時，首頁仍可用展示資料呈現專題概念
$featured_noodles = [];
if (empty($db_error)) {
    $result = $conn->query("SELECT * FROM noodles ORDER BY stock DESC LIMIT 6");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $featured_noodles[] = $row;
        }
    }
}

if (empty($featured_noodles)) {
    $featured_noodles = [
        ['code' => 'N005', 'name' => 'Maruchan Chicken', 'brand' => 'Maruchan', 'price' => 1.99, 'stock' => 200],
        ['code' => 'N006', 'name' => 'Cup Noodles', 'brand' => 'Nissin', 'price' => 2.49, 'stock' => 150],
        ['code' => 'N007', 'name' => 'Sapporo Ichiban', 'brand' => 'Sapporo', 'price' => 2.29, 'stock' => 120],
        ['code' => 'N002', 'name' => 'Indomie Mi Goreng', 'brand' => 'Indomie', 'price' => 3.99, 'stock' => 100],
        ['code' => 'N003', 'name' => 'Mama Tom Yum', 'brand' => 'Mama', 'price' => 2.99, 'stock' => 75],
        ['code' => 'N008', 'name' => 'Nissin Raoh', 'brand' => 'Nissin', 'price' => 3.49, 'stock' => 60],
    ];
}

// AI 修改：為熱門商品配置一致風格圖片與推薦屬性
$noodle_visuals = [
    'N002' => ['image' => 'N002-mi-goreng.webp', 'spice' => 'medium', 'style' => 'dry', 'mood' => 'energy'],
    'N003' => ['image' => 'N003-tom-yum.webp', 'spice' => 'hot', 'style' => 'sour', 'mood' => 'adventure'],
    'N005' => ['image' => 'N005-chicken.webp', 'spice' => 'mild', 'style' => 'classic', 'mood' => 'comfort'],
    'N006' => ['image' => 'N006-cup.webp', 'spice' => 'mild', 'style' => 'light', 'mood' => 'quick'],
    'N007' => ['image' => 'N007-shoyu.webp', 'spice' => 'mild', 'style' => 'soy', 'mood' => 'comfort'],
    'N008' => ['image' => 'N008-tonkotsu.webp', 'spice' => 'medium', 'style' => 'rich', 'mood' => 'reward'],
];

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
        <header>
            <div class="logo">
                <h1>🍜 Staffless Instant Noodle Store</h1>
                <p>Grab → Scan → Pay → Go!</p>
            </div>
            <div class="nav-links">
                <a href="index.php"><span aria-hidden="true">🏪</span> Home</a>
                <a href="#how-it-works"><span aria-hidden="true">⚡</span> How It Works</a>
                <a href="#kiosk-demo"><span aria-hidden="true">📱</span> Kiosk Demo</a>
                <a href="#ai-recommend"><span aria-hidden="true">✨</span> AI Recommendation</a>
                <a href="#menu"><span aria-hidden="true">🍜</span> Menu</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"><span aria-hidden="true">🧾</span> Dashboard</a>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <a href="admin/index.php"><span aria-hidden="true">⚙️</span> Admin</a>
                    <?php endif; ?>
                    <a href="logout.php"><span aria-hidden="true">↪</span> Logout</a>
                <?php else: ?>
                    <a href="login.php"><span aria-hidden="true">🔐</span> Login</a>
                    <a href="register.php"><span aria-hidden="true">✚</span> Register</a>
                <?php endif; ?>
            </div>
        </header>
        
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
                    <a href="#kiosk-demo" class="btn btn-secondary">Try Kiosk Demo</a>
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
                <article class="timeline-item">
                    <span>01</span>
                    <h3>Scan shelf QR</h3>
                    <p>Each noodle slot has a code and QR tag so customers can identify products without staff.</p>
                </article>
                <article class="timeline-item">
                    <span>02</span>
                    <h3>Add by code</h3>
                    <p>The website checks noodle code, quantity, price and stock before adding to cart.</p>
                </article>
                <article class="timeline-item">
                    <span>03</span>
                    <h3>Pay online</h3>
                    <p>Checkout simulates card payment and records transaction status for the admin panel.</p>
                </article>
                <article class="timeline-item">
                    <span>04</span>
                    <h3>Pickup unlock</h3>
                    <p>After payment, the order receives a pickup code that represents the unmanned pickup counter.</p>
                </article>
            </div>
        </section>

        <section class="kiosk-demo-section" id="kiosk-demo">
            <div class="kiosk-copy">
                <p class="eyebrow">INTERACTIVE DEMO</p>
                <h2>Self-service kiosk simulation</h2>
                <p>Choose a noodle below and watch the kiosk preview update. This is a lightweight demo for presentation, while the real order flow continues through login, cart and checkout.</p>
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
                    <p data-demo-desc>Tap a scan button to simulate a customer at the shelf.</p>
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
                <span class="section-description">Choose three preferences and let the local recommendation model find your best match.</span>
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
                    <p class="ai-disclaimer">Demo recommendation runs locally and does not upload personal data.</p>
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
                <div class="info">Demo menu is showing because the database is not reachable in this environment.</div>
            <?php endif; ?>
            <div class="noodle-grid">
                <?php foreach ($featured_noodles as $noodle):
                    $visual = $noodle_visuals[$noodle['code']] ?? $noodle_visuals['N005'];
                ?>
                <div class="noodle-card" data-noodle-product
                     data-code="<?php echo htmlspecialchars($noodle['code']); ?>"
                     data-spice="<?php echo htmlspecialchars($visual['spice']); ?>"
                     data-style="<?php echo htmlspecialchars($visual['style']); ?>"
                     data-mood="<?php echo htmlspecialchars($visual['mood']); ?>">
                    <div class="noodle-image-wrap">
                        <img src="assets/images/noodles/<?php echo htmlspecialchars($visual['image']); ?>"
                             alt="<?php echo htmlspecialchars($noodle['name']); ?>"
                             class="noodle-image" loading="lazy">
                        <span class="product-availability">● LIVE STOCK</span>
                    </div>
                    <h3><?php echo htmlspecialchars($noodle['name']); ?></h3>
                    <p class="brand"><?php echo htmlspecialchars($noodle['brand']); ?></p>
                    <p class="code">Code: <strong><?php echo htmlspecialchars($noodle['code']); ?></strong></p>
                    <p class="price">$<?php echo number_format($noodle['price'], 2); ?></p>
                    <p class="stock">In Stock: <?php echo $noodle['stock']; ?></p>
                    <button type="button" class="btn btn-secondary btn-small" data-demo-choice="<?php echo htmlspecialchars($noodle['code'] . '|' . $noodle['name'] . '|' . $noodle['brand']); ?>">Preview Code</button>
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
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
