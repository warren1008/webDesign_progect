<?php
require_once 'includes/config.php';


$admin_username = 'admin';
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);


$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $admin_username);

if ($stmt->execute()) {
    echo "✅ Password for 'admin' has been reset to: <strong>admin123</strong><br>";
    echo "<a href='login.php'>Go to Login Page</a>";
} else {
    echo "❌ Error: " . $conn->error;
}


$check = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($check->num_rows == 0) {

    $stmt2 = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt2->bind_param("sss", $admin_username, 'admin@noodlestore.com', $hashed_password);
    if ($stmt2->execute()) {
        echo "<br>✅ Admin account created!";
    }
}
?>