<?php
require_once 'auth.php';

// Get cart count if logged in
$cartCount = 0;
if (isLoggedIn()) {
    $cartResult = getUserCart($_SESSION['user_id']);
    $cartCount = $cartResult->num_rows;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HerbVeda Wellness</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --premium-gold: #D4AF37;
            --premium-dark: #1a1a1a;
            --premium-light: #f8f5f0;
            --premium-accent: #5d432c;
        }
        
        /* Navbar Styles */
        .navbar {
            background-color: var(--premium-dark);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            padding-top: 15px;
            padding-bottom: 15px;
        }
        
        .navbar.scrolled {
            background-color: var(--premium-dark) !important;
            box-shadow: 0 4px 30px rgba(212, 175, 55, 0.2);
        }
        
        .navbar-brand img {
            max-height: 60px;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled .navbar-brand img {
            max-height: 50px;
        }
        
        .nav-link {
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            color: var(--premium-light);
            margin: 0 12px;
            position: relative;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: var(--premium-gold);
        }
        
        .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--premium-gold);
        }
        
        /* Dropdown Styles - Fixed positioning */
        .dropdown-menu {
            background-color: var(--premium-dark);
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 10px 0;
            margin-top: 10px;
            /* Ensure dropdown appears above other elements */
            z-index: 1000;
        }
        
        .dropdown-item {
            padding: 10px 20px;
            font-family: 'Poppins', sans-serif;
            color: var(--premium-light);
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: var(--premium-gold);
        }
        
        .dropdown-divider {
            border-color: rgba(212, 175, 55, 0.2);
        }
        
        /* Badge Styles */
        .premium-badge {
            background: linear-gradient(135deg, #D4AF37, #F5D78E);
            color: var(--premium-dark);
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .user-type-badge {
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 4px;
            margin-left: 10px;
            float: right;
            letter-spacing: 0.5px;
        }
        
        .user-badge {
            background: linear-gradient(135deg, #007bff, #5ab1ff);
            color: white;
        }
        
        .shopkeeper-badge {
            background: linear-gradient(135deg, #6c757d, #9fa6ad);
            color: white;
        }
        
        .doctor-badge {
            background: linear-gradient(135deg, #dc3545, #ff6b7b);
            color: white;
        }
        
        /* Button Styles */
        .btn-outline-premium {
            color: var(--premium-gold);
            border-color: var(--premium-gold);
            background-color: transparent;
        }
        
        .btn-outline-premium:hover {
            background-color: var(--premium-gold);
            color: var(--premium-dark);
        }
        
        .btn-premium {
            background: linear-gradient(135deg, #D4AF37, #F5D78E);
            color: var(--premium-dark);
            border: none;
            font-weight: 600;
        }
        
        .btn-premium:hover {
            background: linear-gradient(135deg, #C9A227, #E5C77E);
            color: var(--premium-dark);
        }
        
        /* Cart Icon */
        .cart-icon {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .cart-icon:hover {
            color: var(--premium-gold);
            transform: scale(1.1);
        }
        
        /* Animation */
        @keyframes goldPulse {
            0% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(212, 175, 55, 0); }
            100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0); }
        }
        
        .pulse-animation {
            animation: goldPulse 2s infinite;
        }
        
        /* Ensure dropdowns work on hover for desktop */
        @media (min-width: 992px) {
            .dropdown:hover .dropdown-menu {
                display: block;
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.jpeg" alt="HerbVeda Wellness Premium Logo" class="img-fluid">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : '' ?>" href="shop.php">Boutique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" href="about.php">Our Story</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : '' ?>" href="blog.php">Wellness Journal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'consultation.php' ? 'active' : '' ?>" href="consultation.php">Consultation</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if (isLoggedIn()): ?>
                        <!-- User is logged in - show profile dropdown and cart -->
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1" style="color: var(--premium-gold);"></i>
                                <span class="d-none d-lg-inline"><?= htmlspecialchars($_SESSION['name']) ?></span>
                                <?php if (isset($_SESSION['is_premium']) && $_SESSION['is_premium']): ?>
                                    <span class="premium-badge ms-2 pulse-animation">PREMIUM</span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2" style="color: var(--premium-gold);"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2" style="color: var(--premium-gold);"></i>Orders</a></li>
                                <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2" style="color: var(--premium-gold);"></i>Wishlist</a></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'user'): ?>
                                    <li><a class="dropdown-item" href="subscription.php"><i class="fas fa-crown me-2" style="color: var(--premium-gold);"></i>Premium Membership</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2" style="color: var(--premium-gold);"></i>Logout</a></li>
                            </ul>
                        </div>
                        <a href="cart.php" class="nav-link position-relative ms-3 cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cartCount ?></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <!-- User is not logged in - show login/register dropdowns -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-premium dropdown-toggle" type="button" id="loginDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                <span class="d-none d-lg-inline">Login</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="loginDropdown">
                                <li><a class="dropdown-item" href="login.php?type=user">
                                    <i class="fas fa-user me-2" style="color: var(--premium-gold);"></i>Member Login
                                    <span class="user-type-badge user-badge">Member</span>
                                </a></li>
                                <li><a class="dropdown-item" href="admin/login.php">
                                    <i class="fas fa-store me-2" style="color: var(--premium-gold);"></i>Boutique Login
                                    <span class="user-type-badge shopkeeper-badge">Boutique</span>
                                </a></li>
                                <li><a class="dropdown-item" href="doctor/login.php">
                                    <i class="fas fa-user-md me-2" style="color: var(--premium-gold);"></i>Specialist Login
                                    <span class="user-type-badge doctor-badge">Specialist</span>
                                </a></li>
                            </ul>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-premium dropdown-toggle" type="button" id="registerDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-plus me-1"></i>
                                <span class="d-none d-lg-inline">Join Us</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="registerDropdown">
                                <li><a class="dropdown-item" href="register.php?type=user">
                                    <i class="fas fa-user me-2" style="color: var(--premium-gold);"></i>Member Registration
                                    <span class="user-type-badge user-badge">Member</span>
                                </a></li>
                                <li><a class="dropdown-item" href="admin/register.php">
                                    <i class="fas fa-store me-2" style="color: var(--premium-gold);"></i>Boutique Registration
                                    <span class="user-type-badge shopkeeper-badge">Boutique</span>
                                </a></li>
                                <li><a class="dropdown-item" href="doctor/register.php">
                                    <i class="fas fa-user-md me-2" style="color: var(--premium-gold);"></i>Specialist Registration
                                    <span class="user-type-badge doctor-badge">Specialist</span>
                                </a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        document.addEventListener("DOMContentLoaded", function() {
            window.addEventListener("scroll", function() {
                if (window.scrollY > 50) {
                    document.querySelector('.navbar').classList.add('scrolled');
                } else {
                    document.querySelector('.navbar').classList.remove('scrolled');
                }
            });
            
            // Initialize dropdowns - simplified version
            document.querySelectorAll('.dropdown-toggle').forEach(function(dropdownToggle) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    var dropdownMenu = this.nextElementSibling;
                    dropdownMenu.classList.toggle('show');
                    
                    // Close other open dropdowns
                    document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                        if (menu !== dropdownMenu && menu.classList.contains('show')) {
                            menu.classList.remove('show');
                        }
                    });
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.matches('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
                    document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                }
            });
        });
    </script>
</body>
</html>