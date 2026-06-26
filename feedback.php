<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
ensureInnovationSchema();

$message = '';
$error = '';
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = getUserById((int)$_SESSION['user_id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = '表單已過期，請重新整理後再送出。';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $category = trim($_POST['category'] ?? 'experience');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $content = trim($_POST['message'] ?? '');
        $allowedCategories = ['experience', 'ordering', 'pickup', 'reward', 'other'];

        if ($name === '' || $content === '') {
            $error = '請留下姓名與回饋內容。';
        } elseif (!in_array($category, $allowedCategories, true)) {
            $error = '請選擇有效的回饋分類。';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '電子郵件格式不正確。';
        } else {
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $stmt = $conn->prepare("INSERT INTO feedback_messages (user_id,name,email,category,rating,message) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('isssis', $userId, $name, $email, $category, $rating, $content);
            $message = $stmt->execute()
                ? '謝謝你的回饋，我們已送到後台處理清單。'
                : '回饋送出失敗，請稍後再試。';
        }
    }
}

$categoryLabels = [
    'experience' => '整體體驗',
    'ordering' => '點餐流程',
    'pickup' => '取餐與門市',
    'reward' => '點數與優惠',
    'other' => '其他建議',
];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>使用回饋－無人拉麵商店</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
<div class="container">
    <?php include 'includes/navbar.php'; ?>

    <section class="feedback-hero">
        <div>
            <p class="eyebrow" data-en="SERVICE FEEDBACK" data-zh="服務回饋">服務回饋</p>
            <h1 data-en="Tell us where the unmanned flow can feel smoother" data-zh="讓無人點餐流程變得更順手">讓無人點餐流程變得更順手</h1>
            <p data-en="Share what felt convenient, confusing, or worth improving. The admin team can review every note from the dashboard."
               data-zh="你可以留下覺得方便、卡住或值得改進的地方，後台會集中接收並追蹤處理狀態。">你可以留下覺得方便、卡住或值得改進的地方，後台會集中接收並追蹤處理狀態。</p>
        </div>
        <div class="feedback-metrics" aria-label="Feedback highlights">
            <span><strong>5</strong><small>快速評分</small></span>
            <span><strong>24H</strong><small>後台可追蹤</small></span>
            <span><strong>FAQ</strong><small>改善依據</small></span>
        </div>
    </section>

    <?php if ($message): ?><div class="success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section class="feedback-layout">
        <form method="POST" class="feedback-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken()); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>姓名</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>電子郵件</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>回饋分類</label>
                    <select name="category">
                        <?php foreach ($categoryLabels as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>體驗評分</label>
                    <select name="rating">
                        <option value="5">5 分，很順暢</option>
                        <option value="4">4 分，整體不錯</option>
                        <option value="3">3 分，普通</option>
                        <option value="2">2 分，有點卡</option>
                        <option value="1">1 分，需要改善</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>回饋內容</label>
                <textarea name="message" rows="6" placeholder="例如：點數兌換很清楚，但取餐碼頁面可以再明顯一點。" required></textarea>
            </div>
            <button type="submit" name="submit_feedback" class="btn btn-primary">送出回饋</button>
        </form>

        <aside class="feedback-side-panel">
            <h2>可以回饋什麼？</h2>
            <ul>
                <li>點餐、加料、購物車是否好理解</li>
                <li>付款、取餐碼、門市資訊是否清楚</li>
                <li>點數商城與活動優惠是否吸引人</li>
                <li>中英文切換是否自然</li>
            </ul>
            <p>這個功能會把使用者聲音接回後台，讓管理員不只看訂單，也能看服務體驗。</p>
        </aside>
    </section>

    <?php include 'includes/footer.php'; ?>
</div>
<script src="assets/app.js"></script>
</body>
</html>
