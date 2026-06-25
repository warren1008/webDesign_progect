<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();

$user = getUserById($_SESSION['user_id']);
$message = '';
$error = '';
$lastOrder = null;
$stmt = $conn->prepare("
    SELECT o.id, o.order_number, o.created_at,
           GROUP_CONCAT(CONCAT(n.name, ' ×', oi.quantity) SEPARATOR '、') AS items
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN noodles n ON n.id = oi.noodle_id
    WHERE o.user_id = ? AND o.payment_status = 'paid'
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 1
");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$lastOrder = $stmt->get_result()->fetch_assoc();
$kioskToken = 'KIOSK-' . strtoupper(substr(hash_hmac('sha256', (string)$_SESSION['user_id'], csrfToken()), 0, 16));

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $email = trim($_POST['email']);
        
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $user = getUserById($_SESSION['user_id']);
        } else {
            $error = "Email already exists or invalid.";
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if (password_verify($current_password, $user_data['password'])) {
            if ($new_password === $confirm_password && strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $message = "Password changed successfully!";
                } else {
                    $error = "Failed to change password.";
                }
            } else {
                $error = "New password must be at least 6 characters and match confirmation.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <header>
            <div class="logo">
                <h1 data-en="My Profile" data-zh="我的會員資料">My Profile</h1>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" data-en="Dashboard" data-zh="點餐台">Dashboard</a>
                <a href="order-history.php" data-en="Orders" data-zh="訂單紀錄">Orders</a>
                <a href="logout.php" data-en="Logout" data-zh="登出">Logout</a>
            </div>
        </header>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="profile-layout">
            <div class="profile-info">
                <h2 data-en="Profile Information" data-zh="會員資料">Profile Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label data-en="Username" data-zh="使用者名稱">Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small data-en="Username cannot be changed" data-zh="使用者名稱不可修改">Username cannot be changed</small>
                    </div>
                    <div class="form-group">
                        <label data-en="Email Address" data-zh="電子郵件">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label data-en="Account Type" data-zh="帳號類型">Account Type</label>
                        <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label data-en="Member Since" data-zh="加入日期">Member Since</label>
                        <input type="text" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary" data-en="Update Profile" data-zh="更新會員資料">Update Profile</button>
                </form>
            </div>
            
            <div class="change-password">
                <h2 data-en="Change Password" data-zh="變更密碼">Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label data-en="Current Password" data-zh="目前密碼">Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label data-en="New Password" data-zh="新密碼">New Password</label>
                        <input type="password" name="new_password" required>
                        <small data-en="Minimum 6 characters" data-zh="至少 6 個字元">Minimum 6 characters</small>
                    </div>
                    <div class="form-group">
                        <label data-en="Confirm New Password" data-zh="確認新密碼">Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-secondary" data-en="Change Password" data-zh="更新密碼">Change Password</button>
                </form>
            </div>
        </div>

        <div class="profile-innovation-grid">
            <section class="innovation-card kiosk-login-card">
                <p class="eyebrow">KIOSK QUICK LOGIN</p>
                <h2 data-en="Kiosk Lightning Login" data-zh="機台閃電登入">Kiosk Lightning Login</h2>
                <div class="profile-qr" data-profile-qr="<?php echo htmlspecialchars($kioskToken); ?>"></div>
                <p data-en="Show this rotating demo credential at the unmanned kiosk to load your member profile."
                   data-zh="在實體無人機台前出示此展示條碼，即可模擬秒速登入並載入最愛點餐。">Show this rotating demo credential at the unmanned kiosk to load your member profile.</p>
                <small>DEMO TOKEN · 不含密碼與原始 user_id</small>
            </section>
            <section class="innovation-card reorder-card">
                <p class="eyebrow">ONE-TAP REORDER</p>
                <h2 data-en="Repeat My Last Order" data-zh="一鍵複製上一單">Repeat My Last Order</h2>
                <?php if ($lastOrder): ?>
                    <strong><?php echo htmlspecialchars($lastOrder['order_number']); ?></strong>
                    <p><?php echo htmlspecialchars($lastOrder['items']); ?></p>
                    <button type="button" class="btn btn-primary" data-reorder-last
                            data-en="Add This Order to Cart" data-zh="複製這一單到購物車">Add This Order to Cart</button>
                <?php else: ?>
                    <p data-en="Complete your first order to unlock one-tap reorder."
                       data-zh="完成第一筆訂單後即可使用一鍵重複點餐。">Complete your first order to unlock one-tap reorder.</p>
                <?php endif; ?>
            </section>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
