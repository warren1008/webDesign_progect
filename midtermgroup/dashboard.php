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

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $noodle_code = strtoupper(trim($_POST['noodle_code']));
    $quantity = (int)$_POST['quantity'];
    
    $noodle = getNoodleByCode($noodle_code);
    
    if ($noodle) {
        if ($noodle['stock'] >= $quantity && $quantity > 0) {
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
            $error = "Insufficient stock for {$noodle['name']}. Only {$noodle['stock']} available.";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        
        <div class="dashboard-layout">
            <div class="enter-noodle">
                <h2>📱 Enter Noodle Code</h2>
                <div class="instruction">
                    <p>Find the code on the noodle package and enter it below:</p>
                </div>
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="noodle_code" placeholder="Example: N001" required>
                        <input type="number" name="quantity" placeholder="Qty" min="1" value="1" required>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                    </div>
                </form>
                
                <div class="noodle-reference">
                    <h3>Available Noodle Codes:</h3>
                    <div class="code-list">
                        <?php
                        $result = $conn->query("SELECT code, name, stock FROM noodles ORDER BY code");
                        while ($n = $result->fetch_assoc()):
                        ?>
                        <div class="code-item">
                            <strong><?php echo $n['code']; ?></strong> - <?php echo $n['name']; ?>
                            <span class="stock-badge"><?php echo $n['stock']; ?> left</span>
                        </div>
                        <?php endwhile; ?>
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
</body>
</html>