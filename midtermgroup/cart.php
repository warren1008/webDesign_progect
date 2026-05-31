<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$cart_total = getCartTotal();
$cart_count = getCartCount();
$message = '';
$error = '';

// Handle remove
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
        $message = "Item removed from cart";
    }
    header('Location: cart.php');
    exit();
}

// Handle clear
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $message = "Cart cleared";
    header('Location: cart.php');
    exit();
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } elseif (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
    header('Location: cart.php');
    exit();
}

// Check if coming from checkout with error
if (isset($_GET['error']) && $_GET['error'] == 'payment_failed') {
    $error = 'Payment failed. Please try again or update your cart.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <h1>🛒 Your Shopping Cart</h1>
            </div>
            <div class="nav-links">
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
                        </table>
                    </thead>
                    <tbody>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <span class="product-icon">🍜</span>
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo $item['code']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="99" class="qty-input">
                            </td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td><a href="?remove=<?php echo $id; ?>" class="remove-btn" onclick="return confirm('Remove this item?')">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4"><strong>Total:</strong></td>
                            <td colspan="2"><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="btn btn-secondary">🔄 Update Cart</button>
                    <a href="?clear=1" class="btn btn-danger" onclick="return confirm('Clear entire cart?')">🗑️ Clear Cart</a>
                    <a href="checkout.php" class="btn btn-success">✅ Proceed to Checkout →</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>