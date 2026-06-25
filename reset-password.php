<?php
require_once 'includes/password-reset.php';

$rawToken = $_POST['token'] ?? $_GET['token'] ?? '';
$validToken = findValidResetToken($rawToken);
$message = '';
$error = '';
$completed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'The form expired. Please refresh and try again.';
    } elseif (($_POST['password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
        $error = 'Passwords do not match.';
    } else {
        $result = completePasswordReset($rawToken, $_POST['password'] ?? '');
        if ($result['success']) {
            $message = $result['message'];
            $completed = true;
            $validToken = null;
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
    <title>Reset Password - Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
    <main class="auth-page">
        <div class="auth-form password-reset-card">
            <div class="auth-icon" aria-hidden="true">&#128274;</div>
            <p class="eyebrow">SECURE RESET</p>
            <h1 data-en="Create a new password" data-zh="設定新密碼">Create a new password</h1>

            <?php if ($message): ?>
                <div class="success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($validToken && !$completed): ?>
                <p>
                    <span data-en="Resetting password for" data-zh="正在重設帳號">Resetting password for</span>
                    <strong><?php echo htmlspecialchars($validToken['email']); ?></strong>
                </p>
                <form method="POST" data-password-reset-form>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($rawToken); ?>">
                    <label for="new-password" data-en="New password" data-zh="新密碼">New password</label>
                    <input id="new-password" type="password" name="password" minlength="8"
                           autocomplete="new-password" data-new-password required>
                    <div class="password-strength" data-password-strength>
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <small data-en="Use at least 8 characters with letters and numbers."
                           data-zh="至少 8 個字元，並包含英文字母與數字。">
                        Use at least 8 characters with letters and numbers.
                    </small>
                    <label for="confirm-password" data-en="Confirm password" data-zh="確認新密碼">Confirm password</label>
                    <input id="confirm-password" type="password" name="confirm_password"
                           autocomplete="new-password" required>
                    <button type="submit" class="btn btn-success"
                            data-en="Update Password" data-zh="更新密碼">Update Password</button>
                </form>
            <?php elseif (!$completed): ?>
                <div class="error" data-en="This reset link is invalid, expired, or already used."
                     data-zh="此重設連結無效、已過期或已使用。">
                    This reset link is invalid, expired, or already used.
                </div>
                <a class="btn btn-primary" href="forgot-password.php"
                   data-en="Request Another Link" data-zh="重新申請連結">Request Another Link</a>
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
