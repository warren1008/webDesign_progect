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
$points_to_use = max(0, (int)($_POST['points_to_use'] ?? 0));
$benefits = calculateCartBenefits($cart_items, $points_to_use, (int)$_SESSION['user_id']);
$invoice_carrier = trim($_POST['invoice_carrier'] ?? '');
$tax_id = preg_replace('/\D/', '', $_POST['tax_id'] ?? '');
$error = '';
$success = '';

// Process payment when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $card_type = $_POST['card_type'] ?? '';
    $card_number = preg_replace('/\s/', '', $_POST['card_number'] ?? '');
    $card_expiry = $_POST['card_expiry'] ?? '';
    $card_cvv = $_POST['card_cvv'] ?? '';
    
    // Validate card details
    // AI 修改：新增 Sandbox 電子收據欄位驗證，但不寫入正式發票系統。
    if (empty($card_type) || empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
        $error = 'Please fill in all payment details';
    } elseif ($invoice_carrier !== '' && !preg_match('/^\/[A-Z0-9.+-]{7}$/i', $invoice_carrier)) {
        $error = '請輸入正確的手機條碼格式，例如 /AB12CDE；此欄位僅作模擬收據展示。';
    } elseif ($tax_id !== '' && !preg_match('/^\d{8}$/', $tax_id)) {
        $error = '統一編號需為 8 位數字；此欄位僅作模擬收據展示。';
    } else {
        // Process payment
        $payment_result = processPayment($benefits['final_total'], $card_number, $card_expiry, $card_cvv, $card_type);
        
        if ($payment_result['success']) {
            // Create order
            $order_result = createOrder($_SESSION['user_id'], $_SESSION['cart'], 'credit_card', $card_type, $benefits);
            
            if ($order_result['success']) {
                // Record payment
                recordPayment($order_result['order_id'], $benefits['final_total'], $card_type, 'success', $payment_result['transaction_id']);
                if ($benefits['points_used'] > 0) {
                    changeUserPoints(
                        (int)$_SESSION['user_id'],
                        -$benefits['points_used'],
                        'redeem',
                        'Order point discount',
                        (int)$order_result['order_id']
                    );
                }
                if ($benefits['points_earned'] > 0) {
                    changeUserPoints(
                        (int)$_SESSION['user_id'],
                        $benefits['points_earned'],
                        'earn',
                        'Points earned from order',
                        (int)$order_result['order_id']
                    );
                }
                
                // Clear cart
                $_SESSION['cart'] = [];
                
                // Store order info for success page
                $_SESSION['last_order'] = [
                    'order_number' => $order_result['order_number'],
                    'pickup_code' => $order_result['pickup_code'],
                    'total' => $benefits['final_total'],
                    'subtotal' => $benefits['subtotal'],
                    'discount' => $benefits['discount'],
                    'points_used' => $benefits['points_used'],
                    'points_earned' => $benefits['points_earned'],
                    'invoice_carrier' => $invoice_carrier,
                    'tax_id' => $tax_id,
                    'receipt_note' => 'Sandbox 模擬收據資訊，未串接正式電子發票平台。',
                    'card_type' => $card_type,
                    'transaction_id' => $payment_result['transaction_id'],
                    'items' => $cart_items
                ];
                
                header('Location: order-success.php');
                exit();
            } else {
                $error = $order_result['message'] ?? 'Failed to create order. Please try again.';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <header>
            <div class="logo">
                <h1>💳 Checkout</h1>
            </div>
            <div class="nav-links">
                <a href="index.php">🏪 Store</a>
                <a href="cart.php">← Back to Cart</a>
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </header>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="flow-progress" aria-label="Checkout progress">
            <!-- AI 修改：補上無人商店付款流程提示，讓 demo 流程更清楚 -->
            <div class="flow-step is-done"><span>1</span><strong>Code</strong><small>Enter noodle code</small></div>
            <div class="flow-step is-done"><span>2</span><strong>Cart</strong><small>Confirm quantity</small></div>
            <div class="flow-step is-active"><span>3</span><strong>Pay</strong><small>Card simulation</small></div>
            <div class="flow-step"><span>4</span><strong>Pickup</strong><small>Show pickup code</small></div>
        </section>
        
        <div class="checkout-layout">
            <div class="order-summary">
                <h2>📋 Order Summary</h2>
                <p class="section-note">Confirm your items before the self-service shelf unlocks the pickup code.</p>
                <table class="summary-table">
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?>
                            <?php if (!empty($item['customization'])): ?>
                                <small class="summary-customization"><?php echo htmlspecialchars(customizationSummary($item['customization'])); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td><strong>商品小計</strong></td>
                        <td><strong>$<?php echo number_format($benefits['subtotal'], 2); ?></strong></td>
                    </tr>
                    <?php if ($benefits['promotion_discount'] > 0): ?>
                    <tr class="discount-row">
                        <td>活動折扣</td>
                        <td>-$<?php echo number_format($benefits['promotion_discount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($benefits['point_discount'] > 0): ?>
                    <tr class="discount-row">
                        <td>點數折抵（<?php echo $benefits['points_used']; ?> P）</td>
                        <td>-$<?php echo number_format($benefits['point_discount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td><strong>實付金額</strong></td>
                        <td><strong>$<?php echo number_format($benefits['final_total'], 2); ?></strong></td>
                    </tr>
                </table>
                <div class="checkout-benefits">
                    <span>本次預計獲得 <strong><?php echo $benefits['points_earned']; ?> P</strong></span>
                    <?php foreach ($benefits['applied_promotions'] as $promotion): ?>
                        <span><?php echo htmlspecialchars($promotion['title_zh']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="payment-form">
                <h2>💳 Payment Method</h2>
                <form method="POST" data-payment-form>
                    <div class="points-redeem-box">
                        <label for="points-to-use">會員點數折抵</label>
                        <div>
                            <input id="points-to-use" type="number" name="points_to_use" min="0"
                                   max="<?php echo $benefits['point_balance']; ?>"
                                   step="10" value="<?php echo $benefits['points_used']; ?>">
                            <span>可用 <?php echo $benefits['point_balance']; ?> P</span>
                            <button type="submit" class="btn btn-secondary btn-small" name="preview_points">套用點數</button>
                        </div>
                        <small>10 點折 NT$1，單筆最多折抵 50%。</small>
                    </div>
                    <div class="card-types">
                        <label class="card-option">
                            <input type="radio" name="card_type" value="visa" data-demo-card="4111111111111111" checked required> 
                            <span>💳 Visa</span>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="card_type" value="mastercard" data-demo-card="5555555555554444" required> 
                            <span>💳 Mastercard</span>
                        </label>
                        <label class="card-option">
                            <input type="radio" name="card_type" value="amex" data-demo-card="378282246310005" required> 
                            <span>💳 American Express</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" placeholder="4111 1111 1111 1111" maxlength="19" inputmode="numeric" data-card-number required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5" inputmode="numeric" data-card-expiry required>
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" name="card_cvv" placeholder="123" maxlength="4" inputmode="numeric" data-card-cvv required>
                        </div>
                    </div>

                    <div class="kiosk-preview" data-payment-preview>
                        <span class="kiosk-dot"></span>
                        <strong>Self-service kiosk waiting for secure payment...</strong>
                    </div>

                    <div class="sandbox-receipt-box">
                        <h3 data-en="Sandbox receipt options" data-zh="模擬電子收據選項">Sandbox receipt options</h3>
                        <p data-en="These fields only appear on the demo receipt and do not connect to an official invoice or tax service."
                           data-zh="以下欄位只會顯示在展示收據，不會串接正式電子發票或稅務服務。">These fields only appear on the demo receipt and do not connect to an official invoice or tax service.</p>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="invoice-carrier">手機條碼載具</label>
                                <input id="invoice-carrier" type="text" name="invoice_carrier"
                                       value="<?php echo htmlspecialchars($invoice_carrier); ?>"
                                       placeholder="/AB12CDE" maxlength="8">
                            </div>
                            <div class="form-group">
                                <label for="tax-id">統一編號</label>
                                <input id="tax-id" type="text" name="tax_id"
                                       value="<?php echo htmlspecialchars($tax_id); ?>"
                                       placeholder="12345678" maxlength="8" inputmode="numeric">
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-actions">
                        <button type="submit" name="process_payment" class="btn btn-success">Pay $<?php echo number_format($benefits['final_total'], 2); ?></button>
                        <a href="cart.php" class="btn btn-secondary">Cancel & Back to Cart</a>
                    </div>
                </form>
                
                <div class="demo-note">
                    <p>💡 <strong>Demo Mode:</strong> Visa starts with 4, Mastercard starts with 5, Amex starts with 3</p>
                    <p>Example: <strong>4111 1111 1111 1111</strong> | 12/28 | 123</p>
                    <button type="button" class="btn btn-secondary btn-small" data-fill-demo-card>Fill Demo Card</button>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
