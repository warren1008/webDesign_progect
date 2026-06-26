<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/innovation.php';
requireAdmin();
ensureInnovationSchema();

$user = getUserById($_SESSION['user_id']);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $currentAvatar = (string)($user['avatar_path'] ?? '');
        $upload = projectUploadImage('avatar_image', 'assets/images/avatars', 'admin-' . $_SESSION['user_id'], $currentAvatar);
        if (!$upload['success']) {
            $error = $upload['message'];
        } else {
            $avatarPath = $upload['path'];
            $stmt = $conn->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
            $stmt->bind_param("si", $avatarPath, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $message = "Avatar updated successfully!";
                $user = getUserById($_SESSION['user_id']);
            } else {
                $error = "Failed to update avatar.";
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
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
                }
            } else {
                $error = "New password must be at least 6 characters and match confirmation.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
$avatarSrc = displayImagePath($user['avatar_path'] ?? '', 'assets/images/avatars/avatar-default.svg');
if (!preg_match('/^https?:\/\//i', $avatarSrc)) {
    $avatarSrc = '../' . ltrim($avatarSrc, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Noodle Store</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1 data-en="Admin Profile" data-zh="管理員資料">Admin Profile</h1>
            <?php include 'includes/admin_nav.php'; ?>
        </header>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="profile-layout">
            <div class="profile-info">
                <h2 data-en="Admin Information" data-zh="管理員資訊">Admin Information</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="profile-avatar-card admin-avatar-card">
                        <img class="profile-avatar-image"
                             src="<?php echo htmlspecialchars($avatarSrc); ?>"
                             alt="Admin avatar">
                        <div class="profile-avatar-meta">
                            <strong data-en="Admin Avatar" data-zh="管理員頭像">Admin Avatar</strong>
                            <span data-en="Use a square JPG, PNG, WEBP or GIF under 2MB."
                                  data-zh="建議使用正方形圖片，JPG、PNG、WEBP 或 GIF，2MB 以內。">Use a square JPG, PNG, WEBP or GIF under 2MB.</span>
                            <label class="btn btn-secondary btn-small avatar-upload-button">
                                <span data-en="Choose Avatar" data-zh="選擇頭像">Choose Avatar</span>
                                <input type="file" name="avatar_image" accept="image/jpeg,image/png,image/webp,image/gif">
                            </label>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-small"
                                    data-en="Update Avatar" data-zh="更新頭像">Update Avatar</button>
                        </div>
                    </div>
                </form>
                <div class="info-card">
                    <p><strong data-en="Username:" data-zh="使用者名稱：">Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong data-en="Email:" data-zh="電子郵件：">Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong data-en="Role:" data-zh="角色：">Role:</strong> <span class="admin-badge" data-en="Administrator" data-zh="管理員">Administrator</span></p>
                    <p><strong data-en="Member Since:" data-zh="加入日期：">Member Since:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                </div>
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
                    <button type="submit" name="change_password" class="btn btn-primary"
                            data-en="Change Password" data-zh="更新密碼">Change Password</button>
                </form>
            </div>
        </div>
        <?php include 'includes/admin_footer.php'; ?>
    </div>
    <script src="../assets/app.js"></script>
</body>
</html>
