<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$catalog = getCustomizationCatalog();
$noodles = [];
if (empty($db_error)) {
    $result = $conn->query("SELECT * FROM noodles WHERE stock > 0 ORDER BY code");
    while ($result && $row = $result->fetch_assoc()) {
        $noodles[] = $row;
    }
}
if (!$noodles) {
    $noodles = getDemoNoodles();
}

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_custom_bowl'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'The form expired. Please refresh and try again.';
    } else {
        $noodle = getNoodleById((int)($_POST['noodle_id'] ?? 0));
        $quantity = max(1, min(5, (int)($_POST['quantity'] ?? 1)));
        $custom = buildCustomization($_POST);

        if (!$noodle || !$custom['success']) {
            $error = $custom['message'] ?? 'The selected noodle is unavailable.';
        } elseif (getCartNoodleQuantity($noodle['id']) + $quantity > (int)$noodle['stock']) {
            $error = 'The selected quantity exceeds the available stock.';
        } else {
            $key = cartItemKey($noodle['id'], $custom['selection']);
            $price = round((float)$noodle['price'] + $custom['extra'], 2);
            if (isset($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$key] = [
                    'id' => (int)$noodle['id'],
                    'code' => $noodle['code'],
                    'name' => $noodle['name'] . ' Custom Bowl',
                    'price' => $price,
                    'quantity' => $quantity,
                    'customization' => $custom['selection'],
                ];
            }
            $message = 'Your custom ramen was added to the cart.';
        }
    }
}

$defaultNoodle = $noodles[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramen Lab - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>
    <header>
        <div class="logo">
            <p class="eyebrow">SMART RAMEN LAB</p>
            <h1 data-en="Build Your Ramen" data-zh="打造專屬拉麵">Build Your Ramen</h1>
        </div>
        <nav>
            <a href="index.php" data-en="Store" data-zh="首頁">Store</a>
            <a href="dashboard.php" data-en="Quick Order" data-zh="快速點餐">Quick Order</a>
            <a href="cart.php" data-en="Cart" data-zh="購物車">Cart (<?php echo getCartCount(); ?>)</a>
            <a href="order-history.php" data-en="Orders" data-zh="訂單">Orders</a>
            <a href="logout.php" data-en="Logout" data-zh="登出">Logout</a>
        </nav>
    </header>

    <?php if ($message): ?><div class="success"><?php echo htmlspecialchars($message); ?> <a href="cart.php">View cart</a></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section class="customizer-shell">
        <form method="POST" class="customizer-controls" data-ramen-customizer>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
            <input type="hidden" name="add_custom_bowl" value="1">

            <div class="customizer-heading">
                <span>01</span>
                <div>
                    <h2 data-en="Choose and tune every layer" data-zh="選擇並調整每一層風味">Choose and tune every layer</h2>
                    <p data-en="The price and bowl preview update instantly."
                       data-zh="價格與拉麵預覽會即時更新。">The price and bowl preview update instantly.</p>
                </div>
            </div>

            <label class="custom-field">
                <span data-en="Base noodle" data-zh="基底泡麵">Base noodle</span>
                <select name="noodle_id" data-base-noodle>
                    <?php foreach ($noodles as $noodle): ?>
                        <option value="<?php echo (int)$noodle['id']; ?>"
                                data-price="<?php echo htmlspecialchars($noodle['price']); ?>"
                                data-code="<?php echo htmlspecialchars($noodle['code']); ?>">
                            <?php echo htmlspecialchars($noodle['code'] . ' - ' . $noodle['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <?php
            $selectGroups = [
                'broth' => ['Broth', '湯底'],
                'noodle' => ['Noodle texture', '麵體'],
                'size' => ['Bowl size', '份量'],
            ];
            foreach ($selectGroups as $group => [$en, $zh]):
            ?>
                <label class="custom-field">
                    <span data-en="<?php echo $en; ?>" data-zh="<?php echo $zh; ?>"><?php echo $en; ?></span>
                    <select name="<?php echo $group; ?>" data-custom-price>
                        <?php foreach ($catalog[$group] as $key => $option): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>"
                                    data-price="<?php echo htmlspecialchars($option['price']); ?>">
                                <?php echo htmlspecialchars($option['label']); ?>
                                <?php if ($option['price'] > 0): ?> +$<?php echo number_format($option['price'], 2); ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>

            <fieldset class="spice-selector">
                <legend data-en="Spice level" data-zh="辣度">Spice level</legend>
                <?php foreach ($catalog['spice'] as $key => $option): ?>
                    <label>
                        <input type="radio" name="spice" value="<?php echo $key; ?>"
                               data-price="<?php echo $option['price']; ?>"
                               <?php echo $key === '1' ? 'checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($option['label']); ?></span>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <fieldset class="topping-selector">
                <legend data-en="Toppings" data-zh="配料">Toppings</legend>
                <?php foreach ($catalog['toppings'] as $key => $option): ?>
                    <label>
                        <input type="checkbox" name="toppings[]" value="<?php echo htmlspecialchars($key); ?>"
                               data-price="<?php echo htmlspecialchars($option['price']); ?>">
                        <span><?php echo htmlspecialchars($option['label']); ?></span>
                        <small>+$<?php echo number_format($option['price'], 2); ?></small>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <label class="custom-field quantity-field">
                <span data-en="Quantity" data-zh="數量">Quantity</span>
                <input type="number" name="quantity" min="1" max="5" value="1" data-custom-quantity>
            </label>

            <button type="submit" class="btn btn-success custom-add-button">
                <span data-en="Add Custom Bowl" data-zh="加入客製拉麵">Add Custom Bowl</span>
                <strong data-custom-total>$<?php echo number_format($defaultNoodle['price'], 2); ?></strong>
            </button>
        </form>

        <div class="ramen-preview-panel">
            <div class="scanner-label"><span></span> LIVE FLAVOR RENDER</div>
            <div class="ramen-bowl" data-ramen-bowl>
                <div class="steam steam-one"></div>
                <div class="steam steam-two"></div>
                <div class="bowl-rim">
                    <div class="broth-surface">
                        <span class="noodle-lines"></span>
                        <span class="preview-egg"></span>
                        <span class="preview-seaweed"></span>
                        <span class="preview-corn"></span>
                        <span class="preview-chashu"></span>
                        <span class="preview-scallion"></span>
                    </div>
                </div>
            </div>
            <div class="flavor-readout">
                <div><span data-en="Product code" data-zh="商品代碼">Product code</span><strong data-custom-code><?php echo htmlspecialchars($defaultNoodle['code']); ?></strong></div>
                <div><span data-en="Flavor intensity" data-zh="風味強度">Flavor intensity</span><strong data-flavor-level>42%</strong></div>
                <div><span data-en="Estimated prep" data-zh="預估製作">Estimated prep</span><strong data-prep-time>03:20</strong></div>
            </div>
            <div class="flavor-meter"><span data-flavor-meter></span></div>
        </div>
    </section>
    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
<script src="assets/feature-pages.js"></script>
<script src="assets/customizer.js"></script>
</body>
</html>
