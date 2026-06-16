<?php
// AI 修改：電商式三層導覽，保留 nav-links 結構讓語言與幣別切換器可正常插入。
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);
$isAdminUser = ($_SESSION['user_role'] ?? '') === 'admin';
$cartCount = function_exists('getCartCount') ? getCartCount() : 0;
$memberTarget = $isLoggedIn ? 'member-center.php' : 'login.php';
$orderTarget = $isLoggedIn ? 'dashboard.php' : 'login.php?next=dashboard.php';
$labTarget = $isLoggedIn ? 'customize.php' : 'login.php?next=customize.php';
$navLinks = [
    ['href' => 'promotions.php', 'en' => 'Latest Deals', 'zh' => '最新活動'],
    ['href' => 'index.php#menu', 'en' => 'Best Sellers', 'zh' => '熱銷推薦'],
    ['href' => $orderTarget, 'en' => 'Smart Ordering', 'zh' => '智能點餐', 'mega' => true],
    ['href' => $labTarget, 'en' => 'Ramen Lab', 'zh' => '拉麵實驗室'],
    ['href' => 'rewards.php', 'en' => 'Points Mall', 'zh' => '點數商城'],
    ['href' => 'locations.php', 'en' => 'Cloud Command', 'zh' => '雲端戰情室'],
    ['href' => 'partners.php', 'en' => 'Partner District', 'zh' => '合作商圈'],
    ['href' => 'store-info.php', 'en' => 'Device Monitor', 'zh' => '設備監控'],
    ['href' => $memberTarget, 'en' => 'Member Center', 'zh' => '會員中心'],
];
?>
<header class="site-header commerce-header" data-commerce-header>
    <div class="commerce-announcement" data-commerce-announcement role="status">
        <span data-en="This week: late-night topping bonus and sandbox point rewards are live."
              data-zh="本週活動：深夜加料優惠與 Sandbox 點數回饋上線。">This week: late-night topping bonus and sandbox point rewards are live.</span>
        <button type="button" data-close-announcement aria-label="Close announcement">CLOSE X</button>
    </div>

    <div class="commerce-brand-row">
        <a class="commerce-logo" href="<?php echo htmlspecialchars(appPath('index.php')); ?>" aria-label="Staffless Ramen Store">
            <span aria-hidden="true">🍜</span>
            <strong>Staffless Ramen</strong>
            <small>Alpha 2.0 Sandbox</small>
        </a>
        <div class="commerce-tools" aria-label="Quick tools">
            <a href="<?php echo htmlspecialchars(appPath('index.php#ai-recommend')); ?>" data-en="⌕ Search" data-zh="⌕ 搜尋">⌕ Search</a>
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo htmlspecialchars(appPath('member-center.php')); ?>" data-en="👤 Member" data-zh="👤 會員">👤 Member</a>
                <a href="<?php echo htmlspecialchars(appPath('logout.php')); ?>" class="logout-link" data-en="↪ Logout" data-zh="↪ 登出">↪ Logout</a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars(appPath('login.php')); ?>" data-en="👤 Login" data-zh="👤 登入">👤 Login</a>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars(appPath('rewards.php')); ?>" data-en="♥ Wishlist" data-zh="♥ 收藏點數">♥ Wishlist</a>
            <a href="<?php echo htmlspecialchars(appPath('cart.php')); ?>" class="cart-link" data-en="🛒 Cart (<?php echo $cartCount; ?>)" data-zh="🛒 購物車 (<?php echo $cartCount; ?>)">🛒 Cart (<?php echo $cartCount; ?>)</a>
            <button type="button"
                    class="commerce-collapse-toggle"
                    data-nav-collapse-toggle
                    data-collapse-en="▴ Collapse Menu"
                    data-collapse-zh="▴ 收合選單"
                    data-expand-en="▾ Expand Menu"
                    data-expand-zh="▾ 展開選單"
                    aria-expanded="true">▴ Collapse Menu</button>
            <?php if ($isAdminUser): ?>
                <a href="<?php echo htmlspecialchars(appPath('admin/index.php')); ?>" data-en="⚙ Admin" data-zh="⚙ 管理後台">⚙ Admin</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="nav-links commerce-category-row" aria-label="Primary navigation">
        <?php foreach ($navLinks as $link):
            $href = appPath($link['href']);
            $active = $currentPage === basename(parse_url($link['href'], PHP_URL_PATH) ?: '') ? ' is-active' : '';
            if (!empty($link['mega'])):
        ?>
            <button type="button"
                    class="commerce-nav-button<?php echo $active; ?>"
                    data-mega-trigger
                    aria-expanded="false"
                    aria-controls="smart-order-mega"
                    data-en="<?php echo htmlspecialchars($link['en']); ?>"
                    data-zh="<?php echo htmlspecialchars($link['zh']); ?>"><?php echo htmlspecialchars($link['en']); ?></button>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($href); ?>"
               class="<?php echo trim($active); ?>"
               data-en="<?php echo htmlspecialchars($link['en']); ?>"
               data-zh="<?php echo htmlspecialchars($link['zh']); ?>"><?php echo htmlspecialchars($link['en']); ?></a>
        <?php endif; endforeach; ?>
    </nav>

    <section class="smart-mega-menu" id="smart-order-mega" data-mega-menu aria-label="Smart ordering menu">
        <div class="mega-column">
            <p class="eyebrow" data-en="Best Sellers" data-zh="熱銷主打">Best Sellers</p>
            <a href="<?php echo htmlspecialchars(appPath('dashboard.php?code=N001')); ?>">N001 辛拉麵 · 經典辣湯</a>
            <a href="<?php echo htmlspecialchars(appPath('dashboard.php?code=N004')); ?>">N004 火辣雞麵 · 夜間熱銷</a>
            <a href="<?php echo htmlspecialchars(appPath('dashboard.php?code=N008')); ?>">N008 豚骨拉麵 · 高湯系</a>
        </div>
        <div class="mega-column">
            <p class="eyebrow" data-en="Topping Bay" data-zh="智能配料加購艙">Topping Bay</p>
            <a href="<?php echo htmlspecialchars(appPath('dashboard.php')); ?>">加大麵量 / 溏心蛋 / 起司片</a>
            <a href="<?php echo htmlspecialchars(appPath('customize.php')); ?>">進入拉麵實驗室客製湯底</a>
            <a href="<?php echo htmlspecialchars(appPath('rewards.php')); ?>">用會員點數兌換限定配料</a>
        </div>
        <div class="mega-card">
            <span class="status-pill is-live" data-en="Neon Campaign" data-zh="霓虹活動">Neon Campaign</span>
            <h3 data-en="Late-night ramen bundle" data-zh="深夜拉麵加購組">Late-night ramen bundle</h3>
            <p data-en="Sandbox deals, simulated rewards and unmanned pickup flow are connected to the current cart."
               data-zh="Sandbox 優惠、模擬點數與無人取餐流程會串進目前購物車。">Sandbox deals, simulated rewards and unmanned pickup flow are connected to the current cart.</p>
            <a class="btn btn-primary btn-small" href="<?php echo htmlspecialchars(appPath('promotions.php')); ?>" data-en="View Deals" data-zh="查看活動">View Deals</a>
        </div>
    </section>
</header>
