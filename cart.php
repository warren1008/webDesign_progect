<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$cart_total = getCartTotal();
$cart_count = getCartCount();
$benefits = calculateCartBenefits($_SESSION['cart'] ?? [], 0, (int)$_SESSION['user_id']);
$message = '';
$error = '';


if (isset($_GET['remove'])) {
    $key = preg_replace('/[^A-Za-z0-9-]/', '', (string)$_GET['remove']);
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
        $message = "Item removed from cart";
    }
    header('Location: cart.php');
    exit();
}


if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $message = "Cart cleared";
    header('Location: cart.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $key => $qty) {
        $key = preg_replace('/[^A-Za-z0-9-]/', '', (string)$key);
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$key]);
        } elseif (isset($_SESSION['cart'][$key])) {

            $noodle = getNoodleById((int)$_SESSION['cart'][$key]['id']);
            if ($noodle) {
                $available = (int)$noodle['stock'];
                $otherQuantity = getCartNoodleQuantity($noodle['id']) - (int)$_SESSION['cart'][$key]['quantity'];
                $availableForItem = max(0, $available - $otherQuantity);
                if ($available <= 0) {
                    unset($_SESSION['cart'][$key]);
                } else {
                    $_SESSION['cart'][$key]['quantity'] = min($qty, $availableForItem);
                }
            }
        }
    }
    header('Location: cart.php');
    exit();
}


if (isset($_GET['error']) && $_GET['error'] == 'payment_failed') {
    $error = 'Payment failed. Please try again or update your cart.';
} elseif (isset($_GET['error']) && $_GET['error'] == 'cart_empty') {
    $error = 'Your cart is empty. Add noodles before checkout.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <header>
            <div class="logo">
                <h1>🛒 Your Shopping Cart</h1>
            </div>
            <div class="nav-links">
                <a href="index.php">🏪 Store</a>
                <a href="dashboard.php">← Continue Shopping</a>
                <a href="order-history.php">📦 Orders</a>
                <a href="profile.php">👤 Profile</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="flow-progress" aria-label="Shopping progress">

            <div class="flow-step is-done"><span>1</span><strong>Code</strong><small>Enter noodle code</small></div>
            <div class="flow-step is-active"><span>2</span><strong>Cart</strong><small>Update quantity</small></div>
            <div class="flow-step"><span>3</span><strong>Pay</strong><small>Card simulation</small></div>
            <div class="flow-step"><span>4</span><strong>Pickup</strong><small>Show pickup code</small></div>
        </section>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart-full">
                <div class="empty-icon">🛒</div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any noodles yet.</p>
                <div class="actions">
                    <a href="dashboard.php" class="btn btn-primary">Browse Noodles</a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST">
                <table class="cart-table-full">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <span class="product-icon">🍜</span>
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    <?php if (!empty($item['customization'])): ?>
                                        <small class="cart-customization"><?php echo htmlspecialchars(customizationSummary($item['customization'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo $item['code']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="99" class="qty-input" data-live-subtotal data-price="<?php echo htmlspecialchars($item['price']); ?>">
                            </td>
                            <td data-line-subtotal>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td><a href="?remove=<?php echo $id; ?>" class="remove-btn" onclick="return confirm('Remove this item?')">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <?php if ($benefits['promotion_discount'] > 0): ?>
                        <tr class="discount-row">
                            <td colspan="4"><strong>活動自動折扣：</strong></td>
                            <td colspan="2"><strong>-$<?php echo number_format($benefits['promotion_discount'], 2); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td colspan="4"><strong>優惠後預估：</strong></td>
                            <td colspan="2"><strong data-cart-total>$<?php echo number_format($benefits['final_total'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                <?php if (!empty($benefits['applied_promotions'])): ?>
                    <div class="applied-promotion-list">
                        <strong>已套用優惠</strong>
                        <?php foreach ($benefits['applied_promotions'] as $promotion): ?>
                            <span><?php echo htmlspecialchars($promotion['title_zh']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="btn btn-secondary">🔄 Update Cart</button>
                    <a href="?clear=1" class="btn btn-danger" onclick="return confirm('Clear entire cart?')">🗑️ Clear Cart</a>
                    <a href="checkout.php" class="btn btn-success">✅ Proceed to Checkout →</a>
                </div>
            </form>
        <?php endif; ?>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
