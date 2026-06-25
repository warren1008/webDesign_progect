<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';
$next = $_POST['next'] ?? $_GET['next'] ?? 'dashboard.php';
// AI 修改：只允許導回點餐台與合法商品代碼，避免任意外部重新導向
if (!preg_match('/^(?:dashboard\.php(?:\?code=N\d{3})?|customize\.php)$/', $next)) {
    $next = 'dashboard.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($db_error)) {
        $error = 'Login is unavailable while the database is offline. Use the homepage kiosk demo instead.';
    } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: ' . $next);
        }
        exit();
    } else {
        $error = 'Invalid username/email or password';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <div class="auth-form">
            <h2 data-en="🔐 Login to Your Account" data-zh="🔐 登入會員帳號">🔐 Login to Your Account</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" data-login-form>
                <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
                <input type="text" name="username" placeholder="Username or Email" data-login-username
                       data-placeholder-en="Username or Email" data-placeholder-zh="使用者名稱或 Email" required>
                <input type="password" name="password" placeholder="Password" data-login-password
                       data-placeholder-en="Password" data-placeholder-zh="密碼" required>
                <button type="submit" class="btn btn-primary" data-en="Login" data-zh="登入">Login</button>
            </form>
            <p class="forgot-password-link"><a href="forgot-password.php" data-en="Forgot password?" data-zh="忘記密碼？">Forgot password?</a></p>
            <p><span data-en="Don't have an account?" data-zh="還沒有帳號？">Don't have an account?</span> <a href="register.php" data-en="Register here" data-zh="立即註冊">Register here</a></p>
            <p><a href="index.php" data-en="← Back to store introduction" data-zh="← 返回商店介紹">← Back to store introduction</a></p>
            <div class="demo-credentials">
                <p><strong data-en="Demo Credentials:" data-zh="Demo 測試帳號：">Demo Credentials:</strong></p>
                <p>User: john_doe / user123</p>
                <p>Admin: admin / admin123</p>
                <div class="auth-buttons">
                    <button type="button" class="btn btn-secondary btn-small" data-fill-demo-login="user" data-en="Fill User Demo" data-zh="帶入一般會員">Fill User Demo</button>
                    <button type="button" class="btn btn-success btn-small" data-fill-demo-login="admin" data-en="Fill Admin Demo" data-zh="帶入管理員">Fill Admin Demo</button>
                </div>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
