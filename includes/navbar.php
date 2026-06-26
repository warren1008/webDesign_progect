<?php
// AI 修改：電商式三層導覽，保留 nav-links 結構讓語言與幣別切換器可正常插入。
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);
$isAdminUser = ($_SESSION['user_role'] ?? '') === 'admin';
$cartCount = function_exists('getCartCount') ? getCartCount() : 0;
$memberTarget = $isLoggedIn ? 'member-center.php' : 'login.php';
$orderTarget = $isLoggedIn ? 'dashboard.php' : 'login.php?next=dashboard.php';
$ordersTarget = $isLoggedIn ? 'order-history.php' : 'login.php?next=order-history.php';
$labTarget = $isLoggedIn ? 'customize.php' : 'login.php?next=customize.php';
$navLinks = [
    ['href' => 'promotions.php', 'en' => 'Latest Deals', 'zh' => '最新活動', 'icon' => 'sparkles'],
    ['href' => 'index.php#menu', 'en' => 'Best Sellers', 'zh' => '熱銷推薦', 'icon' => 'flame'],
    ['href' => $orderTarget, 'en' => 'Smart Ordering', 'zh' => '智能點餐', 'icon' => 'qr', 'mega' => true],
    ['href' => $labTarget, 'en' => 'Ramen Lab', 'zh' => '拉麵實驗室', 'icon' => 'lab'],
    ['href' => 'rewards.php', 'en' => 'Points Mall', 'zh' => '點數商城', 'icon' => 'gift'],
    ['href' => 'locations.php', 'en' => 'Store Locations', 'zh' => '門市據點', 'icon' => 'map-pin'],
    ['href' => 'partners.php', 'en' => 'Partner District', 'zh' => '合作商圈', 'icon' => 'handshake'],
    ['href' => 'store-info.php', 'en' => 'Device Monitor', 'zh' => '設備監控', 'icon' => 'monitor'],
    ['href' => 'feedback.php', 'en' => 'Feedback', 'zh' => '使用回饋', 'icon' => 'message'],
    ['href' => $memberTarget, 'en' => 'Member Center', 'zh' => '會員中心', 'icon' => 'user'],
];

function navIcon(string $name): string
{
    $icons = [
        'bowl' => '<path d="M5 13h14"/><path d="M7 13c.8 4 3.1 6 5 6s4.2-2 5-6"/><path d="M8 10h8"/><path d="M10 7h4"/><path d="M9 4c1.3.8 1.3 1.9 0 2.7"/><path d="M13 4c1.3.8 1.3 1.9 0 2.7"/>',
        'search' => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.8-4 4.5-6 8-6s6.2 2 8 6"/>',
        'package' => '<path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="v7c0 1 .6 1.9 1.5 2.3L12 21l7.5-3.7C20.4 16.9 21 16 21 15V8"/><path d="M12 11v10"/>',
        'logout' => '<path d="M10 17v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h3a2 2 0 0 1 2 2v2"/><path d="M15 7l5 5-5 5"/><path d="M8 12h12"/>',
        'login' => '<path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/>',
        'heart' => '<path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0L12 7l-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4L12 21.8l8.8-8.8a5.2 5.2 0 0 0 0-7.4Z"/>',
        'cart' => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.6 12.4A2 2 0 0 0 9.6 17H18a2 2 0 0 0 1.9-1.4L21 8H7"/>',
        'menu' => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a8.2 8.2 0 0 0 .1-1.5 8.2 8.2 0 0 0-.1-1.5l2-1.5-2-3.5-2.4 1a7.5 7.5 0 0 0-2.6-1.5L14 4h-4l-.4 2.5A7.5 7.5 0 0 0 7 8L4.6 7 2.6 10.5l2 1.5a8.2 8.2 0 0 0-.1 1.5c0 .5 0 1 .1 1.5l-2 1.5 2 3.5L7 19a7.5 7.5 0 0 0 2.6 1.5L10 23h4l.4-2.5A7.5 7.5 0 0 0 17 19l2.4 1 2-3.5-2-1.5Z"/>',
        'sparkles' => '<path d="m12 3 1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3Z"/><path d="m5 14 .8 2.2L8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8L5 14Z"/><path d="m19 14 .8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14Z"/>',
        'flame' => '<path d="M12 22c4 0 7-2.8 7-6.8 0-3-1.8-5.1-4.2-7.5-.2 2-1 3.3-2.3 4.3.3-3.2-1.1-5.9-4-8C8.8 7 5 9.7 5 15.2 5 19.2 8 22 12 22Z"/>',
        'qr' => '<path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h2v2h-2z"/><path d="M18 14h2v6h-4v-2h2z"/>',
        'lab' => '<path d="M10 2v6l-5.5 9.4A3 3 0 0 0 7.1 22h9.8a3 3 0 0 0 2.6-4.6L14 8V2"/><path d="M8 2h8"/><path d="M7 17h10"/>',
        'gift' => '<path d="M20 12v9H4v-9"/><path d="M2 7h20v5H2z"/><path d="M12 7v14"/><path d="M12 7H8.5A2.5 2.5 0 1 1 12 3.5V7Z"/><path d="M12 7h3.5A2.5 2.5 0 1 0 12 3.5V7Z"/>',
        'map-pin' => '<path d="M12 22s7-5.3 7-12a7 7 0 1 0-14 0c0 6.7 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/>',
        'handshake' => '<path d="M8 12 4.5 8.5 2 11l4.5 4.5"/><path d="m16 12 3.5-3.5L22 11l-4.5 4.5"/><path d="M7 13l3-3 4 4 3-3"/><path d="m9 15 2 2a2 2 0 0 0 2.8 0l1.2-1.2"/>',
        'monitor' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8"/><path d="M12 16v4"/><path d="m8 11 2.5-2.5L13 11l3-4"/>',
        'message' => '<path d="M21 12a8 8 0 0 1-8 8H6l-4 3v-7a8 8 0 1 1 19-4Z"/><path d="M8 10h8"/><path d="M8 14h5"/>',
    ];
    $paths = $icons[$name] ?? $icons['sparkles'];
    return '<svg class="nav-icon nav-icon-' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}

function navText(string $en, string $zh): string
{
    return '<span class="nav-label" data-en="' . htmlspecialchars($en, ENT_QUOTES, 'UTF-8') . '" data-zh="' . htmlspecialchars($zh, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($en, ENT_QUOTES, 'UTF-8') . '</span>';
}
?>
<header class="site-header commerce-header" data-commerce-header>
    <div class="commerce-announcement" data-commerce-announcement role="status">
        <span data-en="This week: late-night topping bonus and member point rewards are live."
              data-zh="本週活動：深夜加料優惠與會員點數回饋上線。">This week: late-night topping bonus and member point rewards are live.</span>
        <button type="button" data-close-announcement aria-label="Close announcement">CLOSE X</button>
    </div>

    <div class="commerce-brand-row">
        <a class="commerce-logo" href="<?php echo htmlspecialchars(appPath('index.php')); ?>" aria-label="Staffless Ramen Store">
            <span class="commerce-logo-mark" aria-hidden="true"><?php echo navIcon('bowl'); ?></span>
            <strong>Staffless Ramen</strong>
            <small>Self-Service Platform</small>
        </a>
        <div class="commerce-tools" aria-label="Quick tools">
            <a href="<?php echo htmlspecialchars(appPath('index.php#ai-recommend')); ?>"><?php echo navIcon('search') . navText('Search', '搜尋'); ?></a>
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo htmlspecialchars(appPath('member-center.php')); ?>"><?php echo navIcon('user') . navText('Member', '會員'); ?></a>
                <a href="<?php echo htmlspecialchars(appPath($ordersTarget)); ?>"><?php echo navIcon('package') . navText('Orders', '訂單'); ?></a>
                <a href="<?php echo htmlspecialchars(appPath('logout.php')); ?>" class="logout-link"><?php echo navIcon('logout') . navText('Logout', '登出'); ?></a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars(appPath('login.php')); ?>"><?php echo navIcon('login') . navText('Login', '登入'); ?></a>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars(appPath('rewards.php')); ?>"><?php echo navIcon('heart') . navText('Points', '點數'); ?></a>
            <a href="<?php echo htmlspecialchars(appPath('cart.php')); ?>" class="cart-link"><?php echo navIcon('cart') . navText('Cart (' . $cartCount . ')', '購物車 (' . $cartCount . ')'); ?></a>
            <button type="button"
                    class="commerce-collapse-toggle"
                    data-nav-collapse-toggle
                    data-collapse-en="Menu"
                    data-collapse-zh="選單"
                    data-expand-en="Menu"
                    data-expand-zh="選單"
                    aria-expanded="true"><?php echo navIcon('menu') . navText('Menu', '選單'); ?></button>
            <?php if ($isAdminUser): ?>
                <a href="<?php echo htmlspecialchars(appPath('admin/index.php')); ?>"><?php echo navIcon('settings') . navText('Admin', '管理後台'); ?></a>
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
                    aria-label="<?php echo htmlspecialchars($link['en']); ?>"><?php echo navIcon($link['icon']) . navText($link['en'], $link['zh']); ?></button>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($href); ?>"
               class="<?php echo trim($active); ?>"
               aria-label="<?php echo htmlspecialchars($link['en']); ?>"><?php echo navIcon($link['icon']) . navText($link['en'], $link['zh']); ?></a>
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
            <p data-en="Deals, member rewards and unmanned pickup flow are connected to the current cart."
               data-zh="優惠、會員點數與無人取餐流程會串進目前購物車。">Deals, member rewards and unmanned pickup flow are connected to the current cart.</p>
            <a class="btn btn-primary btn-small" href="<?php echo htmlspecialchars(appPath('promotions.php')); ?>" data-en="View Deals" data-zh="查看活動">View Deals</a>
        </div>
    </section>
</header>
