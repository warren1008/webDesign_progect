<?php
require_once 'includes/config.php';

// AI 修改：避免公開網址直接重設 admin 密碼，只允許本機命令列維護時執行
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Password reset utility is disabled from the browser.');
}

// Only run this from CLI when maintenance is needed, then delete the file for security
$admin_username = 'admin';
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $admin_username);

if ($stmt->execute()) {
    echo "✅ Password for 'admin' has been reset to: <strong>admin123</strong><br>";
    echo "<a href='login.php'>Go to Login Page</a>";
} else {
    echo "❌ Error: " . $conn->error;
}

// Check if admin exists
$check = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($check->num_rows == 0) {
    // Create admin if doesn't exist
    $stmt2 = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt2->bind_param("sss", $admin_username, 'admin@noodlestore.com', $hashed_password);
    if ($stmt2->execute()) {
        echo "<br>✅ Admin account created!";
    }
}
?>
