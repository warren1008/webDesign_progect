<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$orderNumber = $_GET['order'] ?? ($_SESSION['last_order']['order_number'] ?? '');
$order = $orderNumber ? getOrderForUser($orderNumber, $_SESSION['user_id']) : null;
if (!$order) {
    header('Location: order-history.php');
    exit();
}

$elapsed = max(0, time() - strtotime($order['created_at']));
$prepSeconds = 28;
$locker = generateLockerNumber($order['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Status - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>
    <header>
        <div class="logo">
            <p class="eyebrow">AUTOMATED KITCHEN</p>
            <h1 data-en="Live Order Status" data-zh="即時製作狀態">Live Order Status</h1>
        </div>
        <nav>
            <a href="dashboard.php" data-en="Order" data-zh="點餐">Order</a>
            <a href="order-history.php" data-en="My Orders" data-zh="我的訂單">My Orders</a>
            <a href="logout.php" data-en="Logout" data-zh="登出">Logout</a>
        </nav>
    </header>

    <section class="kitchen-status-shell" data-kitchen-status
             data-elapsed="<?php echo $elapsed; ?>" data-duration="<?php echo $prepSeconds; ?>"
             data-order-status="<?php echo htmlspecialchars($order['order_status']); ?>">
        <div class="kitchen-main">
            <div class="kitchen-order-meta">
                <span><?php echo htmlspecialchars($order['order_number']); ?></span>
                <strong data-kitchen-state>Preparing</strong>
            </div>
            <div class="prep-visual">
                <div class="prep-machine">
                    <div class="machine-light"></div>
                    <div class="machine-window">
                        <div class="mini-bowl"></div>
                        <div class="water-stream"></div>
                    </div>
                    <div class="machine-display" data-countdown>00:28</div>
                </div>
                <div class="prep-copy">
                    <p class="eyebrow">SMART PREPARATION</p>
                    <h2 data-en="Your ramen is being prepared automatically"
                        data-zh="您的拉麵正在自動製作">
                        Your ramen is being prepared automatically
                    </h2>
                    <p data-kitchen-message
                       data-en="The machine is checking temperature and portion accuracy."
                       data-zh="機台正在確認溫度與份量。">
                        The machine is checking temperature and portion accuracy.
                    </p>
                    <div class="prep-progress"><span data-prep-progress></span></div>
                </div>
            </div>

            <div class="kitchen-stages">
                <div class="kitchen-stage" data-stage="0"><span>1</span><strong data-en="Payment verified" data-zh="付款確認">Payment verified</strong></div>
                <div class="kitchen-stage" data-stage="1"><span>2</span><strong data-en="Heating water" data-zh="加熱注水">Heating water</strong></div>
                <div class="kitchen-stage" data-stage="2"><span>3</span><strong data-en="Flavor assembly" data-zh="組合風味">Flavor assembly</strong></div>
                <div class="kitchen-stage" data-stage="3"><span>4</span><strong data-en="Locker ready" data-zh="取餐櫃就緒">Locker ready</strong></div>
            </div>
        </div>

        <aside class="locker-panel">
            <p class="eyebrow">PICKUP LOCKER</p>
            <div class="locker-door" data-locker-door>
                <div class="locker-screen">
                    <small data-en="LOCKER" data-zh="取餐櫃">LOCKER</small>
                    <strong><?php echo htmlspecialchars($locker); ?></strong>
                    <span data-locker-status>LOCKED</span>
                </div>
            </div>
            <div class="locker-code">
                <span data-en="Pickup code" data-zh="取餐碼">Pickup code</span>
                <strong><?php echo htmlspecialchars($order['pickup_code']); ?></strong>
            </div>
            <p data-en="The locker unlocks automatically when preparation is complete."
               data-zh="製作完成後，取餐櫃將自動解鎖。">
                The locker unlocks automatically when preparation is complete.
            </p>
        </aside>
    </section>
    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
<script src="assets/feature-pages.js"></script>
<script src="assets/kitchen.js"></script>
</body>
</html>
