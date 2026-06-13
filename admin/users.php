<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) { // Prevent admin from deleting themselves
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
</head>
<body>
    <div class="container">
        <header>
            <h1>👥 Manage Users</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="payments.php">Payments</a>
                <a href="profile.php">Profile</a>
                <a href="../logout.php">Logout</a>
            </nav>
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
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">Current Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="../assets/app.js"></script>
</body>
</html>
