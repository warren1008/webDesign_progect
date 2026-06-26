<?php
$adminCurrentPage = basename($_SERVER['PHP_SELF']);

function adminIcon(string $name): string
{
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'analytics' => '<path d="M4 19V5"/><path d="M4 19h17"/><path d="M8 16V9"/><path d="M13 16V6"/><path d="M18 16v-4"/>',
        'products' => '<path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'orders' => '<path d="M6 2h9l3 3v17H6z"/><path d="M14 2v4h4"/><path d="M9 10h6"/><path d="M9 14h6"/><path d="M9 18h4"/>',
        'users' => '<circle cx="9" cy="8" r="4"/><path d="M2.5 21c1.2-4 3.4-6 6.5-6s5.3 2 6.5 6"/><path d="M16 11a3 3 0 1 0 0-6"/><path d="M18 21c-.3-2.2-1.2-3.9-2.8-5"/>',
        'payments' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h4"/>',
        'marketing' => '<path d="M4 14v5a2 2 0 0 0 2 2h2"/><path d="M8 14 20 6v12L8 14Z"/><path d="M8 14H5a2 2 0 0 1 0-4h3"/>',
        'feedback' => '<path d="M21 12a8 8 0 0 1-8 8H5l-3 3v-8a8 8 0 1 1 19-3Z"/><path d="M8 10h8"/><path d="M8 14h5"/>',
        'profile' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.8-4 4.5-6 8-6s6.2 2 8 6"/>',
        'logout' => '<path d="M10 17v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h3a2 2 0 0 1 2 2v2"/><path d="M15 7l5 5-5 5"/><path d="M8 12h12"/>',
    ];
    $paths = $icons[$name] ?? $icons['dashboard'];
    return '<svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}

$adminLinks = [
    ['href' => 'index.php', 'zh' => '點餐台', 'en' => 'Order Kiosk', 'icon' => 'dashboard'],
    ['href' => 'analytics.php', 'zh' => '營運分析', 'en' => 'Analytics', 'icon' => 'analytics'],
    ['href' => 'products.php', 'zh' => '商品管理', 'en' => 'Products', 'icon' => 'products'],
    ['href' => 'orders.php', 'zh' => '訂單', 'en' => 'Orders', 'icon' => 'orders'],
    ['href' => 'users.php', 'zh' => '使用者', 'en' => 'Users', 'icon' => 'users'],
    ['href' => 'payments.php', 'zh' => '付款紀錄', 'en' => 'Payments', 'icon' => 'payments'],
    ['href' => 'marketing.php', 'zh' => '活動會員', 'en' => 'Campaigns', 'icon' => 'marketing'],
    ['href' => 'feedback.php', 'zh' => '顧客回饋', 'en' => 'Feedback', 'icon' => 'feedback'],
    ['href' => 'profile.php', 'zh' => '個人資料', 'en' => 'Profile', 'icon' => 'profile'],
    ['href' => '../logout.php', 'zh' => '登出', 'en' => 'Logout', 'icon' => 'logout'],
];
?>
<nav class="admin-icon-nav" aria-label="Admin navigation">
    <?php foreach ($adminLinks as $link):
        $isActive = $adminCurrentPage === basename($link['href']) ? ' is-active' : '';
    ?>
        <a href="<?php echo htmlspecialchars($link['href']); ?>" class="<?php echo trim($isActive); ?>">
            <?php echo adminIcon($link['icon']); ?>
            <span data-en="<?php echo htmlspecialchars($link['en']); ?>" data-zh="<?php echo htmlspecialchars($link['zh']); ?>"><?php echo htmlspecialchars($link['zh']); ?></span>
        </a>
    <?php endforeach; ?>
</nav>
