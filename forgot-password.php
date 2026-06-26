<?php
require_once 'includes/password-reset.php';

if (isset($_SESSION['user_id'])) {
    redirectTo($_SESSION['user_role'] === 'admin' ? 'admin/index.php' : 'dashboard.php');
}

$message = '';
$error = '';
$resetUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'The form expired. Please refresh and try again.';
    } else {
        $result = requestPasswordReset(
            $_POST['email'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        );
        if ($result['success']) {
            $message = $result['message'];
            $resetUrl = $result['reset_url'] ?? '';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
    <main class="auth-page">
        <div class="auth-form password-reset-card">
            <div class="auth-icon" aria-hidden="true">&#128273;</div>
            <p class="eyebrow">ACCOUNT RECOVERY</p>
            <h1 data-en="Forgot your password?" data-zh="忘記密碼？">Forgot your password?</h1>
            <p data-en="Enter your registered email. The reset link expires in 30 minutes."
               data-zh="輸入註冊信箱，重設連結將在 30 分鐘後失效。">
                Enter your registered email. The reset link expires in 30 minutes.
            </p>

            <?php if ($message): ?>
                <div class="success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" data-password-reset-form>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                <label for="reset-email" data-en="Email address" data-zh="電子郵件">Email address</label>
                <input id="reset-email" type="email" name="email" autocomplete="email"
                       placeholder="you@example.com"
                       data-placeholder-en="Enter your registered email"
                       data-placeholder-zh="請輸入您的註冊信箱" required>
                <button type="submit" class="btn btn-primary"
                        data-en="Prepare Reset Link" data-zh="產生重設連結">Prepare Reset Link</button>
            </form>
            <button type="button" class="btn btn-secondary btn-small demo-email-button"
                    data-fill-demo-email
                    data-en="Fill Account Email" data-zh="快速填入會員信箱">Fill Account Email</button>

            <?php if ($resetUrl): ?>
                <div class="demo-reset-link" data-reset-success>
                    <strong data-en="Secure reset link" data-zh="安全重設連結">Secure reset link</strong>
                    <p data-en="Use this secure link to continue resetting your password."
                       data-zh="請使用此安全連結繼續重設密碼。">
                        Use this secure link to continue resetting your password.
                    </p>
                    <a class="btn btn-success" href="<?php echo htmlspecialchars($resetUrl); ?>"
                       data-en="Open Secure Reset Page" data-zh="開啟安全重設頁">
                        Open Secure Reset Page
                    </a>
                </div>
            <?php endif; ?>

            <a class="auth-back-link" href="login.php"
               data-en="Back to login" data-zh="返回登入">Back to login</a>
        </div>
    </main>
        <?php include 'includes/footer.php'; ?>
    </div>
    <script src="assets/app.js"></script>
    <script src="assets/feature-pages.js"></script>
</body>
</html>
