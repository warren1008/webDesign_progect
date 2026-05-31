<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staffless Instant Noodle Store</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <h1>🍜 Staffless Instant Noodle Store</h1>
                <p>Grab → Scan → Pay → Go!</p>
            </div>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="hero">
            <div class="hero-content">
                <h2>Welcome to the Future of Noodle Shopping</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <p>Grab your favorite instant noodles from our shelves</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <p>Enter the noodle code and quantity on our website</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <p>Pay online with your credit/debit card</p>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <p>Show your pickup code and enjoy your noodles!</p>
                    </div>
                </div>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-primary">Start Shopping →</a>
                </div>
            </div>
        </div>
        
        <div class="featured-noodles">
            <h2>Popular Instant Noodles</h2>
            <div class="noodle-grid">
                <?php
                $result = $conn->query("SELECT * FROM noodles ORDER BY stock DESC LIMIT 6");
                while ($noodle = $result->fetch_assoc()):
                ?>
                <div class="noodle-card">
                    <div class="noodle-icon">🍜</div>
                    <h3><?php echo htmlspecialchars($noodle['name']); ?></h3>
                    <p class="brand"><?php echo htmlspecialchars($noodle['brand']); ?></p>
                    <p class="code">Code: <strong><?php echo htmlspecialchars($noodle['code']); ?></strong></p>
                    <p class="price">$<?php echo number_format($noodle['price'], 2); ?></p>
                    <p class="stock">In Stock: <?php echo $noodle['stock']; ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>