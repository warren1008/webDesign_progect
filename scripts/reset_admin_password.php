<?php
require_once __DIR__ . '/../includes/config.php';

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

$username = $argv[1] ?? 'admin';
$newPassword = $argv[2] ?? '';
if (strlen($newPassword) < 10 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
    exit("Usage: php scripts/reset_admin_password.php admin \"StrongPassword123\"\n");
}

$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND role = 'admin'");
$stmt->bind_param("ss", $hash, $username);
$stmt->execute();

echo $stmt->affected_rows > 0
    ? "Admin password updated.\n"
    : "Admin account not found or password unchanged.\n";
?>
