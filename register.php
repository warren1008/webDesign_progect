<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($db_error)) {
        $error = 'Registration is unavailable while the database is offline.';
    } else {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $success = 'Registration successful! You can now login.';
        } else {
            $error = 'Username or email already exists';
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <div class="auth-form">
            <h2 data-en="📝 Create an Account" data-zh="📝 建立會員帳號">📝 Create an Account</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" data-register-form>
                <input type="text" name="username" placeholder="Username"
                       data-placeholder-en="Username" data-placeholder-zh="使用者名稱" required>
                <input type="email" name="email" placeholder="Email"
                       data-placeholder-en="Email" data-placeholder-zh="電子郵件" required>
                <div class="password-field">
                    <input id="register-password" type="password" name="password" placeholder="Password (min 6 characters)"
                           data-register-password
                           data-placeholder-en="Password (min 6 characters)" data-placeholder-zh="密碼（至少 6 個字元）" required>
                    <button type="button" class="password-toggle" data-toggle-password="#register-password" aria-label="Show password">👁</button>
                </div>
                <div class="password-strength-meter" data-password-meter aria-hidden="true"></div>
                <small class="password-strength-copy">
                    <span data-en="Password strength:" data-zh="密碼強度：">Password strength:</span>
                    <strong data-password-strength-label>Empty</strong>
                </small>
                <div class="password-field">
                    <input id="register-confirm-password" type="password" name="confirm_password" placeholder="Confirm Password"
                           data-confirm-password
                           data-placeholder-en="Confirm Password" data-placeholder-zh="確認密碼" required>
                    <button type="button" class="password-toggle" data-toggle-password="#register-confirm-password" aria-label="Show password">👁</button>
                </div>
                <button type="submit" class="btn btn-primary" data-en="Register" data-zh="註冊">Register</button>
            </form>
            <button type="button" class="btn btn-secondary demo-random-button"
                    data-random-register
                    data-en="Fill Random Demo Data" data-zh="一鍵帶入隨機 Demo 資料">Fill Random Demo Data</button>
            <p><span data-en="Already have an account?" data-zh="已經有帳號？">Already have an account?</span> <a href="login.php" data-en="Login here" data-zh="前往登入">Login here</a></p>
            <p><a href="index.php" data-en="← Back to store introduction" data-zh="← 返回商店介紹">← Back to store introduction</a></p>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
</body>
</html>
