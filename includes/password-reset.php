<?php
require_once __DIR__ . '/functions.php';

const PASSWORD_RESET_MINUTES = 30;
const PASSWORD_RESET_COOLDOWN_SECONDS = 60;

function ensurePasswordResetSchema() {
    global $conn;
    return (bool)$conn->query("
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            requested_ip VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_password_reset_hash (token_hash),
            INDEX idx_password_reset_user (user_id),
            CONSTRAINT fk_password_reset_user
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
}

function requestPasswordReset($email, $requestedIp = '') {
    global $conn, $db_error;
    $generic = 'If the email exists, a password reset link has been prepared.';

    if (!empty($db_error)) {
        return ['success' => false, 'message' => 'Password reset is unavailable while the database is offline.'];
    }
    if (!ensurePasswordResetSchema()) {
        return ['success' => false, 'message' => 'The password reset database update is unavailable.'];
    }

    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => true, 'message' => $generic];
    }

    // AI 修改：localhost 簡報可使用文件指定的 Demo 信箱，但仍重設既有測試會員。
    $isLocal = str_contains($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost')
        || str_contains($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1');
    if ($isLocal && $email === 'demo_user@noodles.com') {
        $email = 'john@example.com';
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) {
        return ['success' => true, 'message' => $generic];
    }

    $userId = (int)$user['id'];
    $stmt = $conn->prepare("
        SELECT created_at FROM password_reset_tokens
        WHERE user_id = ? ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $recent = $stmt->get_result()->fetch_assoc();
    if ($recent && strtotime($recent['created_at']) > time() - PASSWORD_RESET_COOLDOWN_SECONDS) {
        return ['success' => true, 'message' => $generic];
    }

    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    $expiresAt = date('Y-m-d H:i:s', time() + PASSWORD_RESET_MINUTES * 60);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? OR expires_at < NOW()");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $stmt = $conn->prepare("
            INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, requested_ip)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $userId, $tokenHash, $expiresAt, $requestedIp);
        $stmt->execute();
        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Unable to prepare a reset link. Please try again.'];
    }

    $resetUrl = BASE_URL . '/reset-password.php?token=' . urlencode($rawToken);
    if (!$isLocal) {
        $subject = 'Staffless Noodle Store password reset';
        $body = "Open this link within " . PASSWORD_RESET_MINUTES . " minutes:\n" . $resetUrl;
        @mail($email, $subject, $body, 'From: no-reply@stafflessnoodle.infy.click');
    }

    return [
        'success' => true,
        'message' => $generic,
        'demo_url' => $isLocal ? $resetUrl : null,
    ];
}

function findValidResetToken($rawToken) {
    global $conn, $db_error;
    if (!empty($db_error) || !preg_match('/^[a-f0-9]{64}$/', (string)$rawToken)) {
        return null;
    }
    if (!ensurePasswordResetSchema()) {
        return null;
    }

    $tokenHash = hash('sha256', $rawToken);
    $stmt = $conn->prepare("
        SELECT prt.id, prt.user_id, u.email
        FROM password_reset_tokens prt
        JOIN users u ON u.id = prt.user_id
        WHERE prt.token_hash = ?
          AND prt.used_at IS NULL
          AND prt.expires_at >= NOW()
        LIMIT 1
    ");
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

function completePasswordReset($rawToken, $newPassword) {
    global $conn;
    $token = findValidResetToken($rawToken);
    if (!$token) {
        return ['success' => false, 'message' => 'This reset link is invalid or has expired.'];
    }

    if (strlen($newPassword) < 8
        || !preg_match('/[A-Za-z]/', $newPassword)
        || !preg_match('/\d/', $newPassword)) {
        return ['success' => false, 'message' => 'Use at least 8 characters with letters and numbers.'];
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $token['user_id']);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ?");
        $stmt->bind_param("i", $token['user_id']);
        $stmt->execute();
        $conn->commit();
        return ['success' => true, 'message' => 'Password updated. You can now log in.'];
    } catch (Throwable $exception) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Unable to update the password. Please try again.'];
    }
}
?>
