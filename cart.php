<?php
include 'auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $_POST['product_id']);
    $stmt->execute();
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $_POST['quantity'], $_SESSION['user_id'], $_POST['product_id']);
    $stmt->execute();
}

$cartItems = getUserCart($_SESSION['user_id']);
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include your head content from index.html -->
    <title>Your Cart - HerbVeda Hub</title>
</head>
<body>
    <!-- Include your navbar from index.html -->
    
    <div class="container my-5">
        <h2 class="text-center mb-5">Your Shopping Cart</h2>
        
        <?php if ($cartItems->num_rows > 0): ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $cartItems->fetch_assoc()): 
                                        $itemTotal = $item['price'] * $item['quantity'];
                                        $total += $itemTotal;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </div>
                                            </td>
                                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                            <td>
                                                <form method="POST" class="d-flex">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control" style="width: 70px;">
                                                    <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-primary ms-2">Update</button>
                                                </form>
                                            </td>
                                            <td>₹<?php echo number_format($itemTotal, 2); ?></td>
                                            <td>
                                                <form method="POST">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" name="remove_item" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₹<?php echo number_format($total, 2); ?></span>
                            </div>
                            <?php if ($_SESSION['is_premium']): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Premium Discount (15%):</span>
                                    <span>-₹<?php echo number_format($total * 0.15, 2); ?></span>
                                </div>
                                <?php $total = $total * 0.85; ?>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping:</span>
                                <span>FREE</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span>₹<?php echo number_format($total, 2); ?></span>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100 mt-3">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p>Your cart is empty</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Include your footer from index.html -->
</body>
</html>