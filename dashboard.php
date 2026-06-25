<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$error = '';
$selected_code = strtoupper(trim($_GET['code'] ?? ''));
if (!preg_match('/^N\d{3}$/', $selected_code) || !getNoodleByCode($selected_code)) {
    $selected_code = '';
}

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $noodle_code = strtoupper(trim($_POST['noodle_code']));
    $quantity = (int)$_POST['quantity'];
    
    $noodle = getNoodleByCode($noodle_code);
    
    if ($noodle) {
        // AI 修改：一般點餐頁加入加料後，同一泡麵不同加料會成為不同購物車品項。
        $addonResult = buildNoodleAddons($_POST['toppings'] ?? [], $quantity);
        $customization = $addonResult['success'] ? $addonResult['selection'] : [];
        $cart_key = cartItemKey((int)$noodle['id'], $customization);
        $current_quantity = getCartNoodleQuantity($noodle['id']);
        $requested_quantity = $current_quantity + $quantity;

        if (!$addonResult['success']) {
            $error = $addonResult['message'];
        } elseif ($noodle['stock'] >= $requested_quantity && $quantity > 0) {
            $unitPrice = round((float)$noodle['price'] + (float)$addonResult['extra'], 2);
            if (isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$cart_key] = [
                    'id' => $noodle['id'],
                    'code' => $noodle['code'],
                    'name' => $noodle['name'],
                    'base_price' => $noodle['price'],
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'customization' => $customization,
                ];
            }
            $addonText = customizationSummary($customization);
            $message = "已加入 {$quantity}x {$noodle['name']}" . ($addonText ? "（{$addonText}）" : '') . " 到購物車。";
        } else {
            $remaining = max(0, $noodle['stock'] - $current_quantity);
            $error = "Insufficient stock for {$noodle['name']}. You can add {$remaining} more.";
        }
    } else {
        $error = "Noodle code '{$noodle_code}' not found!";
    }
}

// Handle removing from cart
if (isset($_GET['remove'])) {
    // AI 修改：使用實際購物車索引，兼容一般商品與客製拉麵。
    $cart_key = preg_replace('/[^A-Za-z0-9-]/', '', (string)$_GET['remove']);
    unset($_SESSION['cart'][$cart_key]);
    $message = "Item removed from cart";
}

// Handle clearing cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $message = "Cart cleared";
}

$cart_total = getCartTotal();
$cart_count = getCartCount();

// AI 修改：資料庫不可用時改用展示菜單，避免 dashboard 空白
$available_noodles = [];
if (empty($db_error)) {
    $result = $conn->query("SELECT code, name, stock FROM noodles ORDER BY code");
    if ($result) {
        while ($n = $result->fetch_assoc()) {
            $available_noodles[] = $n;
        }
    }
}
if (empty($available_noodles)) {
    $available_noodles = array_map(function ($noodle) {
        return [
            'code' => $noodle['code'],
            'name' => $noodle['name'],
            'stock' => $noodle['stock'],
        ];
    }, getDemoNoodles());
}
$topping_options = getNoodleToppingCatalog();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>

        <section class="dashboard-welcome-strip">
            <!-- AI 修改：共用電商導覽後，保留使用者歡迎與點餐狀態提示。 -->
            <h1>🍜 Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p data-en="Scan a shelf code, add toppings, and send the order to the unmanned pickup flow."
               data-zh="掃描貨架代碼、加選配料，並送進無人取餐流程。">Scan a shelf code, add toppings, and send the order to the unmanned pickup flow.</p>
        </section>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($db_error)): ?>
            <div class="info">Demo menu mode is active. Product browsing still works, while checkout requires the project database.</div>
        <?php endif; ?>

        <section class="flow-progress" aria-label="Ordering progress">
            <!-- AI 修改：補上無人拉麵商店使用流程，對齊 PDF 的 user flow -->
            <div class="flow-step is-active"><span>1</span><strong>Code</strong><small>Enter noodle code</small></div>
            <div class="flow-step"><span>2</span><strong>Cart</strong><small>Confirm quantity</small></div>
            <div class="flow-step"><span>3</span><strong>Pay</strong><small>Card simulation</small></div>
            <div class="flow-step"><span>4</span><strong>Pickup</strong><small>Show pickup code</small></div>
        </section>
        
        <div class="dashboard-layout">
            <div class="enter-noodle">
                <h2>📱 Enter Noodle Code</h2>
                <div class="instruction">
                    <p>Scan the shelf QR code or tap a quick code to simulate the self-service kiosk.</p>
                </div>
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="noodle_code" placeholder="Example: N001" value="<?php echo htmlspecialchars($selected_code); ?>" data-noodle-code-input required>
                        <input type="number" name="quantity" placeholder="Qty" min="1" value="1" required>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                    </div>

                    <section class="addon-panel" data-addon-panel>
                        <!-- AI 修改：參考行動點餐架構，讓泡麵可直接加大與加配料。 -->
                        <div class="addon-panel__head">
                            <div>
                                <p class="eyebrow">SMART TOPPING BAY</p>
                                <h3 data-en="Choose add-ons" data-zh="選擇加料">Choose add-ons</h3>
                                <span data-en="Select up to 6 items. Prices are recalculated by the server."
                                      data-zh="最多選 6 項，加料價格會由後端重新計算。">Select up to 6 items. Prices are recalculated by the server.</span>
                            </div>
                            <strong data-addon-count>已選 0</strong>
                        </div>
                        <div class="addon-grid">
                            <?php foreach ($topping_options as $option): ?>
                                <?php
                                    $soldOut = (int)$option['stock'] <= 0;
                                    $inputId = 'addon-' . preg_replace('/[^a-z0-9-]/', '', strtolower($option['code']));
                                ?>
                                <label class="addon-option<?php echo $soldOut ? ' is-disabled' : ''; ?>" for="<?php echo htmlspecialchars($inputId); ?>">
                                    <input id="<?php echo htmlspecialchars($inputId); ?>"
                                           type="checkbox"
                                           name="toppings[]"
                                           value="<?php echo htmlspecialchars($option['code']); ?>"
                                           data-addon-price="<?php echo htmlspecialchars($option['price']); ?>"
                                           <?php echo $soldOut ? 'disabled' : ''; ?>>
                                    <span class="addon-option__check" aria-hidden="true"></span>
                                    <span class="addon-option__copy">
                                        <strong><?php echo htmlspecialchars($option['name_zh']); ?></strong>
                                        <small><?php echo htmlspecialchars($option['description']); ?></small>
                                        <em>+NT$<?php echo number_format((float)$option['price'] * getUsdTwdRate()); ?> / +$<?php echo number_format((float)$option['price'], 2); ?></em>
                                    </span>
                                    <span class="addon-stock"><?php echo $soldOut ? '售完' : '剩 ' . (int)$option['stock']; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="addon-total">
                            <span data-en="Estimated add-on subtotal" data-zh="加料小計">Estimated add-on subtotal</span>
                            <strong data-addon-total>$0.00</strong>
                        </div>
                    </section>
                </form>

                <div class="kiosk-panel">
                    <!-- AI 修改：新增自助機台狀態感，提升期末展示互動性 -->
                    <div class="kiosk-screen" data-kiosk-screen>
                        <span class="kiosk-dot"></span>
                        <?php if ($selected_code): ?>
                            <strong><?php echo htmlspecialchars($selected_code); ?> loaded</strong>
                            <p>Homepage selection is ready to add to the cart.</p>
                        <?php else: ?>
                            <strong>Scanner ready</strong>
                            <p>Tap a noodle code below to load it into the kiosk.</p>
                        <?php endif; ?>
                    </div>
                    <div class="qr-simulator" aria-label="QR code simulator">
                        <span></span><span></span><span></span><span></span>
                    </div>
                </div>
                
                <div class="noodle-reference">
                    <h3>Available Noodle Codes:</h3>
                    <div class="code-list">
                        <?php foreach ($available_noodles as $n): ?>
                        <button type="button" class="code-item code-button" data-noodle-code="<?php echo htmlspecialchars($n['code']); ?>" data-noodle-name="<?php echo htmlspecialchars($n['name']); ?>">
                            <strong><?php echo $n['code']; ?></strong> - <?php echo $n['name']; ?>
                            <span class="stock-badge"><?php echo $n['stock']; ?> left</span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="shopping-cart">
                <h2>🛒 Your Cart</h2>
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="empty-cart">
                        <p>Your cart is empty.</p>
                        <p>Enter a noodle code above to start shopping!</p>
                    </div>
                <?php else: ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($_SESSION['cart'] as $cart_key => $item): ?>
                            <tr>
                                <td><?php echo $item['code']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    <?php if (!empty($item['customization'])): ?>
                                        <small class="cart-customization"><?php echo htmlspecialchars(customizationSummary($item['customization'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td><a href="?remove=<?php echo urlencode((string)$cart_key); ?>" class="remove-btn" onclick="return confirm('Remove item?')">❌</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="4"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                                <td><a href="?clear=1" class="clear-btn" onclick="return confirm('Clear entire cart?')">Clear All</a></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="cart-actions">
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <a href="cart.php" class="btn btn-secondary">View Full Cart</a>
                            <a href="checkout.php" class="btn btn-success">Proceed to Checkout →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
