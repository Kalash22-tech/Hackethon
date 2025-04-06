<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../includes/dbconnect.php';

// Initialize stats
$products_count = 0;
$pending_orders = 0;
$admin_id = $_SESSION['shop_admin_id'];

// Debug: Verify admin_id exists
if (empty($admin_id)) {
    die("Invalid admin session. Please login again.");
}

// Get product count (simplified)
$product_sql = "SELECT COUNT(*) as total FROM products WHERE shop_admin_id = ?";
$product_stmt = $conn->prepare($product_sql);

if ($product_stmt === false) {
    die("Failed to prepare product query: " . $conn->error);
}

$product_stmt->bind_param("i", $admin_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result) {
    $product_stats = $product_result->fetch_assoc();
    $products_count = $product_stats['total'] ?? 0;
}
$product_stmt->close();

// Get pending orders count (simplified)
$order_sql = "SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'";
$order_stmt = $conn->prepare($order_sql);

if ($order_stmt === false) {
    die("Failed to prepare orders query: " . $conn->error);
}

$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result) {
    $order_stats = $order_result->fetch_assoc();
    $pending_orders = $order_stats['pending'] ?? 0;
}
$order_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }
        .stat-card a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Dashboard <small>Welcome <?php echo htmlspecialchars($_SESSION['shop_admin_username']); ?></small></h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card bg-primary">
                    <h5>Total Products</h5>
                    <h3><?php echo $products_count; ?></h3>
                    <a href="products/list.php">View Products</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card bg-info">
                    <h5>Pending Orders</h5>
                    <h3><?php echo $pending_orders; ?></h3>
                    <a href="orders/list.php?status=pending">Process Orders</a>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_sql = "SELECT o.id, o.order_date, o.status, o.quantity, p.name as product_name 
                                      FROM orders o 
                                      JOIN products p ON o.product_id = p.id 
                                      ORDER BY o.order_date DESC 
                                      LIMIT 5";
                        
                        $recent_result = $conn->query($recent_sql);
                        
                        if ($recent_result && $recent_result->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($order = $recent_result->fetch_assoc()): ?>
                                <a href="orders/view.php?id=<?php echo $order['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <h6><?php echo htmlspecialchars($order['product_name']); ?></h6>
                                        <small><?php echo date('M d, H:i', strtotime($order['order_date'])); ?></small>
                                    </div>
                                    <small>Qty: <?php echo $order['quantity']; ?></small>
                                    <span class="badge bg-<?php 
                                        echo $order['status'] == 'pending' ? 'warning' : 
                                             ($order['status'] == 'processing' ? 'info' : 
                                             ($order['status'] == 'shipped' ? 'primary' : 'success')); 
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No recent orders found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="products/add.php" class="btn btn-primary">Add Product</a>
                            <a href="products/list.php" class="btn btn-secondary">Manage Products</a>
                            <a href="orders/list.php" class="btn btn-info">View Orders</a>
                            <a href="profile.php" class="btn btn-warning">Update Profile</a>
                            <a href="logout.php" class="btn btn-danger">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>