<?php
require_once '../../includes/auth-check.php';
require_once '../../includes/dbconnect.php';

// Handle product status toggle
if (isset($_GET['toggle_status'])) {
    $product_id = $_GET['id'];
    $current_status = $db->query("SELECT is_active FROM products WHERE id = $product_id")->fetch_assoc()['is_active'];
    $new_status = $current_status ? 0 : 1;
    $db->query("UPDATE products SET is_active = $new_status WHERE id = $product_id");
    header("Location: list.php");
    exit();
}

// Handle premium status toggle
if (isset($_GET['toggle_premium'])) {
    $product_id = $_GET['id'];
    $current_status = $db->query("SELECT is_premium FROM products WHERE id = $product_id")->fetch_assoc()['is_premium'];
    $new_status = $current_status ? 0 : 1;
    $db->query("UPDATE products SET is_premium = $new_status WHERE id = $product_id");
    header("Location: list.php");
    exit();
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = $_GET['id'];
    $db->query("DELETE FROM products WHERE id = $product_id");
    header("Location: list.php?success=Product deleted successfully");
    exit();
}

// Fetch all products
$products = $db->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include '../../navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Products</h2>
            <a href="add.php" class="btn btn-success">Add New Product</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Premium</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="../../images/products/<?php echo $product['image']; ?>" width="50" height="50">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>₹<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['discount']; ?>%</td>
                        <td><?php echo $product['stock_quantity']; ?></td>
                        <td>
                            <a href="?toggle_status&id=<?php echo $product['id']; ?>" class="btn btn-sm <?php echo $product['is_active'] ? 'btn-success' : 'btn-danger'; ?>">
                                <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                            </a>
                        </td>
                        <td>
                            <a href="?toggle_premium&id=<?php echo $product['id']; ?>" class="btn btn-sm <?php echo $product['is_premium'] ? 'btn-warning' : 'btn-secondary'; ?>">
                                <?php echo $product['is_premium'] ? 'Premium' : 'Standard'; ?>
                            </a>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
</body>
</html>