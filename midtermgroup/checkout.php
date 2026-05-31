<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php?error=cart_empty');
    exit();
}

$cart_total = getCartTotal();
$cart_items = $_SESSION['cart'];
$error = '';
$success = '';

// Process payment when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $card_type = $_POST['card_type'] ?? '';
    $card_number = preg_replace('/\s/', '', $_POST['card_number'] ?? '');
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';
    
    // Validate card details
    if (empty($card_type) || empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
        $error = 'Please fill in all payment details';
    } else {
        // Process payment
        $payment_result = processPayment($cart_total, $card_number, $card_expiry, $card_cvv);
        
        if ($payment_result['success']) {
            // Create order
            $order_result = createOrder($_SESSION['user_id'], $_SESSION['cart'], 'credit_card', $card_type);
            
            if ($order_result['success']) {
                // Record payment
                recordPayment($order_result['order_id'], $cart_total, $card_type, 'success', $payment_result['transaction_id']);
                
                // Clear cart
                $_SESSION['cart'] = [];
                
                // Store order info for success page
                $_SESSION['last_order'] = [
                    'order_number' => $order_result['order_number'],
                    'pickup_code' => $order_result['pickup_code'],
                    'total' => $cart_total
                ];
                
                header('Location: order-success.php');
                exit();
            } else {
                $error = 'Failed to create order. Please try again.';
            }
        } else {
            $error = $payment_result['message'] ?? 'Payment failed. Please check your card details.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <h1>💳 Checkout</h1>
            </div>
            <div class="nav-links">
                <a href="cart.php">← Back to Cart</a>
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </header>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="checkout-layout">
            <div class="order-summary">
                <h2>📋 Order Summary</h2>
                <table class="summary-table">
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td><strong>Total</strong></td>
                        <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <div class="payment-form">
                <h2>💳 Payment Method</h2>
                <form method="POST">
                    <div class="card-types">
                        <label class="card-option">
                            <input type="radio" name="card_type" value="visa" required> 
                            <span>💳 Visa</span>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="card_type" value="mastercard" required> 
                            <span>💳 Mastercard</span>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="card_type" value="amex" required> 
                            <span>💳 American Express</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" placeholder="4111 1111 1111 1111" maxlength="19" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" name="card_cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>
                    
                    <div class="payment-actions">
                        <button type="submit" name="process_payment" class="btn btn-success">Pay $<?php echo number_format($cart_total, 2); ?></button>
                        <a href="cart.php" class="btn btn-secondary">Cancel & Back to Cart</a>
                    </div>
                </form>
                
                <div class="demo-note">
                    <p>💡 <strong>Demo Mode:</strong> Use any 16-digit card starting with 4 or 5</p>
                    <p>Example: <strong>4111 1111 1111 1111</strong> | 12/25 | 123</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>