<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();
ensureInnovationSchema();

$message = '';
$error = '';
$statuses = ['new' => '新回饋', 'reviewing' => '處理中', 'resolved' => '已完成'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_feedback'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = '表單已過期，請重新整理後再試。';
    } else {
        $id = (int)($_POST['feedback_id'] ?? 0);
        $status = $_POST['status'] ?? 'new';
        $note = trim($_POST['admin_note'] ?? '');
        if (!isset($statuses[$status])) {
            $error = '狀態格式不正確。';
        } else {
            $stmt = $conn->prepare("UPDATE feedback_messages SET status = ?, admin_note = ? WHERE id = ?");
            $stmt->bind_param('ssi', $status, $note, $id);
            $message = $stmt->execute() ? '回饋狀態已更新。' : '更新失敗。';
        }
    }
}

$feedback = $conn->query("
    SELECT f.*, u.username AS member_username
    FROM feedback_messages f
    LEFT JOIN users u ON u.id = f.user_id
    ORDER BY FIELD(f.status,'new','reviewing','resolved'), f.created_at DESC
");

$summaryRows = $conn->query("SELECT status, COUNT(*) AS total FROM feedback_messages GROUP BY status");
$summary = ['new' => 0, 'reviewing' => 0, 'resolved' => 0];
while ($row = $summaryRows->fetch_assoc()) {
    $summary[$row['status']] = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客回饋－管理後台</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
<div class="container admin-container">
    <header>
        <div class="logo">
            <p class="eyebrow">CUSTOMER VOICE</p>
            <h1>顧客回饋中心</h1>
        </div>
        <?php include 'includes/admin_nav.php'; ?>
    </header>

    <?php if ($message): ?><div class="success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section class="feedback-admin-summary">
        <div><span>新回饋</span><strong><?php echo $summary['new']; ?></strong></div>
        <div><span>處理中</span><strong><?php echo $summary['reviewing']; ?></strong></div>
        <div><span>已完成</span><strong><?php echo $summary['resolved']; ?></strong></div>
    </section>

    <section class="feedback-admin-list">
        <?php if (!$feedback || $feedback->num_rows === 0): ?>
            <div class="analytics-panel">
                <h2>目前尚無回饋</h2>
                <p>前台送出使用回饋後，會出現在這裡。</p>
            </div>
        <?php else: ?>
            <?php while ($item = $feedback->fetch_assoc()): ?>
                <article class="feedback-admin-card status-<?php echo htmlspecialchars($item['status']); ?>">
                    <div class="feedback-admin-card__head">
                        <div>
                            <span class="status-badge status-<?php echo htmlspecialchars($item['status']); ?>">
                                <?php echo htmlspecialchars($statuses[$item['status']] ?? $item['status']); ?>
                            </span>
                            <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                            <p>
                                <?php echo htmlspecialchars($item['member_username'] ?: '訪客'); ?> ·
                                <?php echo htmlspecialchars($item['category']); ?> ·
                                <?php echo (int)$item['rating']; ?>/5 ·
                                <?php echo date('Y/m/d H:i', strtotime($item['created_at'])); ?>
                            </p>
                        </div>
                        <?php if (!empty($item['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($item['email']); ?>"><?php echo htmlspecialchars($item['email']); ?></a>
                        <?php endif; ?>
                    </div>
                    <p class="feedback-message"><?php echo nl2br(htmlspecialchars($item['message'])); ?></p>
                    <form method="POST" class="feedback-admin-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
                        <input type="hidden" name="feedback_id" value="<?php echo (int)$item['id']; ?>">
                        <label>處理狀態
                            <select name="status">
                                <?php foreach ($statuses as $value => $label): ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $item['status'] === $value ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>管理備註
                            <input type="text" name="admin_note" value="<?php echo htmlspecialchars($item['admin_note'] ?? ''); ?>" placeholder="例如：已調整文案、列入下版改善">
                        </label>
                        <button type="submit" name="update_feedback" class="btn btn-primary btn-small">更新</button>
                    </form>
                </article>
            <?php endwhile; ?>
        <?php endif; ?>
    </section>
    <?php include 'includes/admin_footer.php'; ?>
</div>
<script src="../assets/app.js"></script>
</body>
</html>
