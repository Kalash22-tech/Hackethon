<?php
require_once '../../includes/auth-check.php';
require_once '../../includes/dbconnect.php';

// Filter parameters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build base query
$query = "SELECT o.*, p.name as product_name, u.username as customer_name 
          FROM orders o
          JOIN products p ON o.product_id = p.id
          JOIN users u ON o.user_id = u.id";

// Add filters
$where = [];
if ($status_filter) {
    $where[] = "o.status = '$status_filter'";
}
if ($date_from) {
    $where[] = "DATE(o.order_date) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(o.order_date) <= '$date_to'";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY o.order_date DESC";
$orders = $db->query($query);

// Handle status update
if (isset($_GET['update_status'])) {
    $order_id = $_GET['order_id'];
    $new_status = $_GET['new_status'];
    $db->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    header("Location: list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include '../../navbar.php'; ?>
    
    <div class="container mt-5">
        <h2>Manage Orders</h2>
        
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="list.php" class="btn btn-secondary ml-2">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>
                            <span class="badge 
                                <?php echo $order['status'] == 'pending' ? 'badge-warning' : 
                                      ($order['status'] == 'processing' ? 'badge-info' : 
                                      ($order['status'] == 'shipped' ? 'badge-primary' : 'badge-success')); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#statusModal<?php echo $order['id']; ?>">
                                        Update Status
                                    </a>
                                    <a class="dropdown-item" href="invoice.php?order_id=<?php echo $order['id']; ?>" target="_blank">
                                        Print Invoice
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Status Update Modal -->
                            <div class="modal fade" id="statusModal<?php echo $order['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Order Status</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="GET">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <div class="form-group">
                                                    <label>New Status</label>
                                                    <select name="new_status" class="form-control">
                                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    </select>
                                                </div>
                                                <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/bootstrap.bundle.min.js"></script>
</body>
</html>