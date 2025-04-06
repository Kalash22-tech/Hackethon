<?php
include 'auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$wishlistItems = getUserWishlist($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include your head content from index.html -->
</head>
<body>
    <!-- Include your navbar from index.html -->
    
    <div class="container my-5">
        <h2 class="text-center mb-5">Your Wishlist</h2>
        <?php if ($wishlistItems->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($item = $wishlistItems->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <p class="price-tag">₹<?php echo number_format($item['price'], 2); ?></p>
                                <a href="productview.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary">View Product</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p>Your wishlist is empty</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Include your footer from index.html -->
</body>
</html>