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
        // AI 修改：重複加入同一商品時檢查購物車累積數量，避免超過庫存
        $current_quantity = $_SESSION['cart'][$noodle['id']]['quantity'] ?? 0;
        $requested_quantity = $current_quantity + $quantity;

        if ($noodle['stock'] >= $requested_quantity && $quantity > 0) {
            if (isset($_SESSION['cart'][$noodle['id']])) {
                $_SESSION['cart'][$noodle['id']]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$noodle['id']] = [
                    'id' => $noodle['id'],
                    'code' => $noodle['code'],
                    'name' => $noodle['name'],
                    'price' => $noodle['price'],
                    'quantity' => $quantity
                ];
            }
            $message = "Added {$quantity}x {$noodle['name']} to cart!";
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
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
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
        <header>
            <div class="logo">
                <h1>🍜 Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            </div>
            <div class="nav-links">
                <a href="index.php">🏪 Store</a>
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="order-history.php">📦 Orders</a>
                <a href="profile.php">👤 Profile</a>
                <a href="logout.php">🚪 Logout</a>
                <a href="cart.php" class="cart-link">🛒 Cart (<?php echo $cart_count; ?>)</a>
            </div>
        </header>
        
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
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td><?php echo $item['code']; ?></td>
                                <td><?php echo $item['name']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td><a href="?remove=<?php echo $item['id']; ?>" class="remove-btn" onclick="return confirm('Remove item?')">❌</a></td>
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
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
