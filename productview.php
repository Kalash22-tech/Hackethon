<?php
include 'dbconnect.php';
session_start();

// Validate and sanitize the product ID
// Validate and sanitize the product ID
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $product_id = intval($_GET['id']);

    try {
        // Fetch product details from database using prepared statements
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $product_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();

        // Check if product exists
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Record product view in analytics
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $ip_address = $_SERVER['REMOTE_ADDR'];
            
            $analytics_sql = "INSERT INTO product_views (product_id, user_id, ip_address) VALUES (?, ?, ?)";
            $analytics_stmt = $conn->prepare($analytics_sql);
            
            if ($analytics_stmt) {
                $analytics_stmt->bind_param("iis", $product_id, $user_id, $ip_address);
                $analytics_stmt->execute();
                $analytics_stmt->close();
            }
        } else {
            header("Location: products.php?error=product_not_found");
            exit();
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred while fetching product details. Please try again later.");
    }
} else {
    header("Location: products.php?error=invalid_product_id");
    exit();
}

// Handle add to cart/wishlist actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if (in_array($action, ['add_to_cart', 'add_to_wishlist'])) {
            if (!isset($_SESSION['user_id'])) {
                // Store for guest user in session
                $item = [
                    'product_id' => $product_id,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'quantity' => 1
                ];
                
                if ($action === 'add_to_cart') {
                    $_SESSION['guest_cart'][] = $item;
                } else {
                    $_SESSION['guest_wishlist'][] = $item;
                }
                
                $response = ['status' => 'success', 'message' => 'Item added to ' . ($action === 'add_to_cart' ? 'cart' : 'wishlist')];
            } else {
                // Store for logged in user in database
                $user_id = $_SESSION['user_id'];
                $table = $action === 'add_to_cart' ? 'cart' : 'wishlist';
                
                // Check if item already exists
                $check_sql = "SELECT * FROM $table WHERE user_id = ? AND product_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $user_id, $product_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update quantity if exists
                    $update_sql = "UPDATE $table SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $user_id, $product_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    // Insert new item
                    $insert_sql = "INSERT INTO $table (user_id, product_id, quantity) VALUES (?, ?, 1)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ii", $user_id, $product_id);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
                
                $check_stmt->close();
                $response = ['status' => 'success', 'message' => 'Item added to ' . ($action === 'add_to_cart' ? 'cart' : 'wishlist')];
            }
            
            echo json_encode($response);
            exit;
        }
    }
}

// Handle FAQ submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faq_question'])) {
    $question = $_POST['faq_question'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : $_POST['email'];
    $name = isset($_SESSION['name']) ? $_SESSION['name'] : $_POST['name'];
    
    $faq_sql = "INSERT INTO faqs (user_id, product_id, name, email, question) VALUES (?, ?, ?, ?, ?)";
    $faq_stmt = $conn->prepare($faq_sql);
    $faq_stmt->bind_param("iisss", $user_id, $product_id, $name, $email, $question);
    
    if ($faq_stmt->execute()) {
        $faq_success = "Your question has been submitted. We'll get back to you soon!";
    } else {
        $faq_error = "There was an error submitting your question. Please try again.";
    }
    
    $faq_stmt->close();
}
?>
<?php if ($_SESSION['is_premium'] ?? false): ?>
    <div class="alert alert-success mt-3">
        <i class="fas fa-crown me-2"></i> As a premium member, you get 15% off this product!
    </div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Premium Ayurvedic Product | HerbVeda Hub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #6a1b9a;
            --accent-color: #ffab00;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            background-color: #f5f5f5;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 18px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            height: 50px;
            transition: all 0.3s ease;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
        }
        
        .jumbotron {
            background: linear-gradient(rgba(0, 0, 0, 0.6), url('<?php echo htmlspecialchars($product['image']); ?>') center/cover no-repeat;
            color: white;
            padding: 120px 20px;
            text-align: center;
            border-radius: 0;
            margin-bottom: 0;
            position: relative;
        }
        
        .product-img-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .product-img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-img-container:hover .product-img {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--accent-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            z-index: 10;
        }
        
        .section-title {
            border-bottom: 3px solid var(--primary-color);
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .action-buttons .btn {
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s ease;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-wishlist {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-wishlist:hover {
            background-color: #f8f9fa;
        }
        
        .price-tag {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .benefit-item {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
        }
        
        .benefit-item:before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--primary-color);
            position: absolute;
            left: 0;
        }
        
        .faq-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .related-products .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .related-products .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .related-products .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .floating-action-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 1000;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .floating-action-btn:hover {
            transform: scale(1.1);
            background-color: #1b5e20;
        }
        
        .premium-feature {
            background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
            border-left: 4px solid var(--accent-color);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
        }
        
        @media (max-width: 768px) {
            .jumbotron {
                padding: 80px 20px;
            }
            
            .product-img {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><img src="images/logo.jpeg" alt="HerbVeda Hub Logo"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="ayurvedicproducts.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                </ul>
                
                <!-- User Actions -->
                <div class="user-actions ms-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a class="user-icon" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <span class="d-none d-md-inline ms-2"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>Orders</a></li>
                                <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                        <a href="register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                    <a href="cart.php" class="user-icon ms-3">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php 
                            $cart_count = 0;
                            if (isset($_SESSION['user_id'])) {
                                $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();
                                $cart_count = $row['total'] ?? 0;
                            } elseif (isset($_SESSION['guest_cart'])) {
                                $cart_count = count($_SESSION['guest_cart']);
                            }
                            echo $cart_count;
                            ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Product Hero Section -->
    <div class="jumbotron">
        <div class="container">
            <h1 class="display-4 animate__animated animate__fadeInDown"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="lead animate__animated animate__fadeInUp"><?php echo htmlspecialchars($product['short_description']); ?></p>
            <div class="mt-4 animate__animated animate__fadeInUp">
                <div class="price-tag">₹<?php echo number_format($product['price'], 2); ?></div>
                <?php if ($product['discount'] > 0): ?>
                    <span class="text-light"><del>₹<?php echo number_format($product['original_price'], 2); ?></del></span>
                    <span class="badge bg-success ms-2"><?php echo $product['discount']; ?>% OFF</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Product Details Section -->
    <div class="container my-5">
        <div class="row">
            <!-- Product Image -->
            <div class="col-lg-5 mb-4">
                <div class="product-img-container">
                    <?php if ($product['is_premium']): ?>
                        <div class="product-badge">PREMIUM</div>
                    <?php endif; ?>
                    <img id="plant-image" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons text-center mt-4">
                    <form id="cartForm" method="POST">
                        <input type="hidden" name="action" value="add_to_cart">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                        </button>
                    </form>
                    <form id="wishlistForm" method="POST">
                        <input type="hidden" name="action" value="add_to_wishlist">
                        <button type="submit" class="btn btn-wishlist btn-lg">
                            <i class="fas fa-heart me-2"></i> Add to Wishlist
                        </button>
                    </form>
                    <form action="checkout.php" method="POST" class="mt-3">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-bolt me-2"></i> Buy Now
                        </button>
                    </form>
                </div>
                
                <!-- Premium Features -->
                <?php if ($product['is_premium']): ?>
                    <div class="premium-feature mt-4">
                        <h5><i class="fas fa-crown text-warning me-2"></i> Premium Benefits</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success me-2"></i> Free shipping</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i> Priority support</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i> Extended warranty</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Details -->
            <div class="col-lg-7">
                <div class="detail-card">
                    <h3 class="section-title">Product Details</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-tag me-2"></i> Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-weight-hanging me-2"></i> Weight:</strong> <?php echo htmlspecialchars($product['weight']); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-box-open me-2"></i> Form:</strong> <?php echo htmlspecialchars($product['form']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-calendar-alt me-2"></i> Shelf Life:</strong> <?php echo htmlspecialchars($product['shelf_life']); ?></p>
                        </div>
                    </div>
                    <p><strong><i class="fas fa-list-ul me-2"></i> Ingredients:</strong> <?php echo htmlspecialchars($product['ingredients']); ?></p>
                </div>
                
                <!-- Health Benefits -->
                <div class="detail-card">
                    <h3 class="section-title"><i class="fas fa-heartbeat me-2"></i> Health Benefits</h3>
                    <div class="benefits-list">
                        <?php 
                        $benefits = explode("\n", $product['health_benefits']);
                        foreach ($benefits as $benefit): 
                            if (!empty(trim($benefit))): ?>
                                <div class="benefit-item"><?php echo htmlspecialchars(trim($benefit)); ?></div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                </div>
                
                <!-- Usage Instructions -->
                <div class="detail-card">
                    <h3 class="section-title"><i class="fas fa-book-open me-2"></i> How to Use</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['how_to_use'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Additional Information Tabs -->
        <div class="row mt-4">
            <div class="col-12">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="diseases-tab" data-bs-toggle="tab" data-bs-target="#diseases" type="button" role="tab">Diseases & Conditions</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="precautions-tab" data-bs-toggle="tab" data-bs-target="#precautions" type="button" role="tab">Precautions</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Customer Reviews</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq" type="button" role="tab">FAQ</button>
                    </li>
                </ul>
                
                <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                    <!-- Diseases & Conditions -->
                    <div class="tab-pane fade show active" id="diseases" role="tabpanel">
                        <h4><i class="fas fa-notes-medical me-2"></i> Diseases & Conditions It Helps With</h4>
                        <p><?php echo nl2br(htmlspecialchars($product['diseases'])); ?></p>
                    </div>
                    
                    <!-- Precautions -->
                    <div class="tab-pane fade" id="precautions" role="tabpanel">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i> Precautions</h4>
                        <p><?php echo nl2br(htmlspecialchars($product['precautions'])); ?></p>
                    </div>
                    
                    <!-- Reviews -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <h4><i class="fas fa-star me-2"></i> Customer Reviews</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5>Rahul Sharma</h5>
                                            <div class="text-warning">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted">Verified Purchase - 2 weeks ago</p>
                                        <p>This product has significantly improved my digestion. I've been using it for a month now and can feel the difference.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5>Priya Patel</h5>
                                            <div class="text-warning">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted">Verified Purchase - 1 month ago</p>
                                        <p>Good quality product, but takes time to show results. Be patient and consistent with usage.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary">Write a Review</button>
                    </div>
                    
                    <!-- FAQ -->
                    <div class="tab-pane fade" id="faq" role="tabpanel">
                        <h4><i class="fas fa-question-circle me-2"></i> Frequently Asked Questions</h4>
                        
                        <div class="accordion mb-4" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                        How long does it take to see results?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Results vary depending on individual body types and conditions. Most customers report noticeable improvements within 2-4 weeks of regular use.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                        Are there any side effects?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        This product is made from 100% natural ingredients and generally has no side effects when taken as directed. However, please consult the precautions section and your doctor if you have specific health conditions.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ Form -->
                        <div class="faq-section">
                            <h5>Have a question?</h5>
                            <p>Ask our Ayurvedic experts about this product</p>
                            
                            <?php if (isset($faq_success)): ?>
                                <div class="alert alert-success"><?php echo $faq_success; ?></div>
                            <?php elseif (isset($faq_error)): ?>
                                <div class="alert alert-danger"><?php echo $faq_error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <textarea class="form-control" name="faq_question" rows="3" placeholder="Your question..." required></textarea>
                                </div>
                                
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-primary">Submit Question</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="section-title mb-4">You May Also Like</h3>
                
                <div class="row related-products">
                    <?php
                    // Fetch related products
                    $related_sql = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4";
                    $related_stmt = $conn->prepare($related_sql);
                    $related_stmt->bind_param("si", $product['category'], $product_id);
                    $related_stmt->execute();
                    $related_result = $related_stmt->get_result();
                    
                    if ($related_result->num_rows > 0):
                        while ($related = $related_result->fetch_assoc()): ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="card">
                                    <a href="productview.php?id=<?php echo $related['id']; ?>">
                                        <img src="<?php echo htmlspecialchars($related['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                    </a>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="productview.php?id=<?php echo $related['id']; ?>"><?php echo htmlspecialchars($related['name']); ?></a>
                                        </h5>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="price-tag">₹<?php echo number_format($related['price'], 2); ?></span>
                                            <a href="productview.php?id=<?php echo $related['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile;
                    else: ?>
                        <div class="col-12">
                            <p>No related products found.</p>
                        </div>
                    <?php endif; 
                    $related_stmt->close(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-action-btn" data-bs-toggle="modal" data-bs-target="#consultModal">
        <i class="fas fa-comment-medical"></i>
    </div>

    <!-- Consultation Modal -->
    <div class="modal fade" id="consultModal" tabindex="-1" aria-labelledby="consultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consultModalLabel">Free Ayurvedic Consultation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Get personalized advice from our certified Ayurvedic doctors about this product and your health concerns.</p>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="book_consultation.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Your Health Concern</label>
                                <textarea class="form-control" name="concern" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Book Free Consultation</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Please <a href="login.php">login</a> or <a href="register.php">register</a> to book a free consultation.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="pt-5 pb-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>HerbVeda Hub</h5>
                    <p>Your trusted source for authentic Ayurvedic products and wisdom since 1995.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white">Home</a></li>
                        <li class="mb-2"><a href="products.php" class="text-white">Products</a></li>
                        <li class="mb-2"><a href="about.php" class="text-white">About Us</a></li>
                        <li class="mb-2"><a href="blog.php" class="text-white">Blog</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Customer Service</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="contact.php" class="text-white">Contact Us</a></li>
                        <li class="mb-2"><a href="faq.php" class="text-white">FAQs</a></li>
                        <li class="mb-2"><a href="shipping.php" class="text-white">Shipping Policy</a></li>
                        <li class="mb-2"><a href="returns.php" class="text-white">Returns & Refunds</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Ayurveda Street, Nature City, India</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +91 9876543210</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> contact@herbvedahub.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 HerbVeda Hub. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <img src="images/payment-methods.png" alt="Payment Methods" class="img-fluid" style="max-height: 30px;">
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle add to cart/wishlist forms
            $('#cartForm, #wishlistForm').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var action = form.find('input[name="action"]').val();
                
                $.ajax({
                    type: 'POST',
                    url: window.location.href,
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Show success message
                            var toast = `<div class="toast show position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                                <div class="toast-header bg-success text-white">
                                    <strong class="me-auto">Success</strong>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                                </div>
                                <div class="toast-body">
                                    ${response.message}
                                </div>
                            </div>`;
                            
                            $('body').append(toast);
                            
                            // Update cart count
                            var cartCount = $('.fa-shopping-cart').next('.badge');
                            var currentCount = parseInt(cartCount.text()) || 0;
                            cartCount.text(currentCount + 1);
                            
                            // Hide toast after 3 seconds
                            setTimeout(function() {
                                $('.toast').remove();
                            }, 3000);
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>