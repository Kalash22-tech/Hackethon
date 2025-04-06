<?php
require_once '../../includes/auth-check.php';
require_once '../../includes/dbconnect.php';

$order_id = $_GET['order_id'];
$order = $db->query("
    SELECT o.*, p.name as product_name, p.price, p.discount, 
           u.username as customer_name, u.email, u.address, u.phone
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE o.id = $order_id
")->fetch_assoc();

// Calculate total
$discounted_price = $order['price'] * (1 - ($order['discount'] / 100));
$total = $discounted_price * $order['quantity'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .table { width: 100%; line-height: inherit; text-align: left; }
        .table td { padding: 5px; vertical-align: top; }
        .table tr.top table td { padding-bottom: 20px; }
        .table tr.information table td { padding-bottom: 40px; }
        .table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .table tr.details td { padding-bottom: 20px; }
        .table tr.item td { border-bottom: 1px solid #eee; }
        .table tr.total td { border-top: 2px solid #eee; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="table">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <h2>Your Ayurveda Store</h2>
                                Invoice #: <?php echo $order_id; ?><br>
                                Created: <?php echo date('F j, Y', strtotime($order['order_date'])); ?><br>
                            </td>
                            <td style="text-align: right;">
                                <img src="../../images/logo.png" style="width:100%; max-width:150px;">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>Billed To:</strong><br>
                                <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                <?php echo htmlspecialchars($order['email']); ?><br>
                                <?php echo htmlspecialchars($order['phone']); ?><br>
                                <?php echo htmlspecialchars($order['address']); ?>
                            </td>
                            <td style="text-align: right;">
                                <strong>Status:</strong> <?php echo ucfirst($order['status']); ?><br>
                                <strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="heading">
                <td>Item</td>
                <td>Price</td>
            </tr>
            
            <tr class="item">
                <td>
                    <?php echo htmlspecialchars($order['product_name']); ?><br>
                    <small>Quantity: <?php echo $order['quantity']; ?></small>
                </td>
                <td>
                    ₹<?php echo number_format($order['price'], 2); ?>
                    <?php if ($order['discount'] > 0): ?>
                        <br><small>(<?php echo $order['discount']; ?>% discount applied)</small>
                    <?php endif; ?>
                </td>
            </tr>
            
            <tr class="total">
                <td></td>
                <td>Total: ₹<?php echo number_format($total, 2); ?></td>
            </tr>
        </table>
        
        <div class="mt-4 no-print" style="text-align: center;">
            <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
            <a href="list.php" class="btn btn-secondary">Back to Orders</a>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Auto-print if coming from status update
            if (window.location.search.includes('print=true')) {
                window.print();
            }
        };
    </script>
</body>
</html>