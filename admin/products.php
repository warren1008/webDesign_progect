<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();
ensureInnovationSchema();

$message = '';
$error = '';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = trim($_POST['description']);
    $upload = projectUploadImage('product_image', 'assets/images/noodles/uploads', $code);
    
    if (!$upload['success']) {
        $error = $upload['message'];
    } else {
        $image = $upload['path'];
        $stmt = $conn->prepare("INSERT INTO noodles (code, name, brand, price, stock, image, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdiss", $code, $name, $brand, $price, $stock, $image, $description);
        
        if ($stmt->execute()) {
            $message = "商品已新增，照片也已同步到前台。";
        } else {
            $error = "Failed to add product. Code may already exist.";
        }
    }
}

// Handle Update Product
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_product = getNoodleById($edit_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = (int)$_POST['product_id'];
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = trim($_POST['description']);
    $currentImage = trim($_POST['current_image'] ?? '');
    $upload = projectUploadImage('product_image', 'assets/images/noodles/uploads', $code, $currentImage);
    
    if (!$upload['success']) {
        $error = $upload['message'];
    } else {
        $image = $upload['path'];
        $stmt = $conn->prepare("UPDATE noodles SET code = ?, name = ?, brand = ?, price = ?, stock = ?, image = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssdissi", $code, $name, $brand, $price, $stock, $image, $description, $id);
        
        if ($stmt->execute()) {
            $message = "商品資料已更新。";
            unset($edit_product);
        } else {
            $error = "Failed to update product.";
        }
    }
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM noodles WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $error = "Failed to delete product.";
    }
}

// Get all products
$products = $conn->query("SELECT * FROM noodles ORDER BY code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1>🍜 Manage Noodle Products</h1>
            <?php include 'includes/admin_nav.php'; ?>
        </header>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="admin-layout">
            <div class="add-product-form">
                <h2><?php echo isset($edit_product) ? '✏️ Edit Product' : '➕ Add New Product'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if (isset($edit_product)): ?>
                        <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_product['image'] ?? ''); ?>">
                    <?php endif; ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Noodle Code</label>
                            <input type="text" name="code" value="<?php echo isset($edit_product) ? htmlspecialchars($edit_product['code']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" value="<?php echo isset($edit_product) ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" value="<?php echo isset($edit_product) ? htmlspecialchars($edit_product['brand']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" value="<?php echo isset($edit_product) ? $edit_product['price'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock" value="<?php echo isset($edit_product) ? $edit_product['stock'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php echo isset($edit_product) ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>商品照片</label>
                        <?php if (!empty($edit_product['image'])): ?>
                            <img class="admin-image-preview" src="../<?php echo htmlspecialchars(displayImagePath($edit_product['image'], 'assets/images/noodles/N005-chicken.webp')); ?>" alt="Current product image">
                        <?php endif; ?>
                        <input type="file" name="product_image" accept="image/png,image/jpeg,image/webp,image/gif">
                        <small>可上傳 JPG、PNG、WEBP、GIF，建議小於 2MB。</small>
                    </div>
                    <button type="submit" name="<?php echo isset($edit_product) ? 'update_product' : 'add_product'; ?>" class="btn btn-primary">
                        <?php echo isset($edit_product) ? 'Update Product' : 'Add Product'; ?>
                    </button>
                    <?php if (isset($edit_product)): ?>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="products-list">
                <h2>📋 All Noodle Products</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products->fetch_assoc()): ?>
                        <tr class="<?php echo $product['stock'] < 10 ? 'low-stock' : ''; ?>"
                            data-product-row="<?php echo (int)$product['id']; ?>">
                            <td><strong><?php echo htmlspecialchars($product['code']); ?></strong></td>
                            <td>
                                <img class="admin-table-thumb" src="../<?php echo htmlspecialchars(displayImagePath($product['image'] ?? '', 'assets/images/noodles/N005-chicken.webp')); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                            <td data-product-price="<?php echo (float)$product['price']; ?>">$<?php echo number_format($product['price'], 2); ?></td>
                            <td data-product-stock="<?php echo (int)$product['stock']; ?>" class="<?php echo $product['stock'] < 10 ? 'stock-warning' : ''; ?>">
                                <?php echo $product['stock']; ?>
                                <?php if ($product['stock'] < 10): ?> ⚠️<?php endif; ?>
                            </td>
                            <td data-row-actions>
                                <button type="button" class="btn-small" data-inline-edit>Edit</button>
                                <button type="button" class="btn-small btn-danger" data-product-delete>Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include 'includes/admin_footer.php'; ?>
    </div>
    <script src="../assets/app.js"></script>
</body>
</html>
