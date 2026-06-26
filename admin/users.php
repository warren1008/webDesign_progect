<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1 class="neon-dynamic-title">👥 Manage Users</h1>
            <?php include 'includes/admin_nav.php'; ?>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><span data-user-track><?php echo htmlspecialchars($user['username']); ?></span></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <button type="button" class="btn-small btn-danger"
                                    data-user-delete="<?php echo (int)$user['id']; ?>">Delete</button>
                        <?php else: ?>
                            <span class="text-muted">Current Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php include 'includes/admin_footer.php'; ?>
    </div>
    <script src="../assets/app.js"></script>
</body>
</html>
