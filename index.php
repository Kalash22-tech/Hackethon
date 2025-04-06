<?php
include 'auth.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loginEmail'])) {
    if (loginUser($_POST['loginEmail'], $_POST['loginPassword'])) {
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $loginError = "Invalid email or password";
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registerEmail'])) {
    if (registerUser($_POST['firstName'].' '.$_POST['lastName'], $_POST['registerEmail'], $_POST['registerPassword'])) {
        loginUser($_POST['registerEmail'], $_POST['registerPassword']);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $registerError = "Registration failed. Email may already be in use.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Get user's wishlist and cart count if logged in
$wishlistCount = 0;
$cartCount = 0;
if (isLoggedIn()) {
    $wishlistResult = getUserWishlist($_SESSION['user_id']);
    $wishlistCount = $wishlistResult->num_rows;
    
    $cartResult = getUserCart($_SESSION['user_id']);
    $cartCount = $cartResult->num_rows;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HerbVeda Hub 🌿 | Premium Ayurveda Solutions</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
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
        
        .navbar-brand img:hover {
            transform: scale(1.05);
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 10px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--accent-color) !important;
        }
        
        .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover:after {
            width: 100%;
        }
        
        .carousel {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .carousel-item img {
            height: 500px;
            object-fit: cover;
        }
        
        .carousel-caption {
            background: rgba(0,0,0,0.6);
            border-radius: 10px;
            padding: 20px;
            bottom: 30%;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .card-body {
            background: white;
        }
        
        .review-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .rating {
            color: var(--accent-color);
            font-size: 1.2rem;
        }
        
        .contact-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        footer {
            background-color: var(--dark-text);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
        
        .premium-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-icon {
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-icon:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }
        
        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
        }
        
        .dropdown-item {
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }
        
        .auth-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-outline-light {
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .newsletter {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 50px 0;
            color: white;
            border-radius: 15px;
            margin: 50px 0;
        }
        
        .newsletter input {
            border-radius: 30px;
            padding: 15px 20px;
            border: none;
        }
        
        .newsletter .btn {
            border-radius: 30px;
            padding: 15px 30px;
            font-weight: 600;
            background-color: var(--accent-color);
            color: var(--dark-text);
            border: none;
        }
        
        .newsletter .btn:hover {
            background-color: #ffc107;
        }
        
        .testimonial-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-color);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: var(--dark-text);
            font-weight: 600;
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
        
        @media (max-width: 768px) {
            .carousel-item img {
                height: 300px;
            }
            
            .carousel-caption {
                bottom: 20%;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
   
  <?php include 'navbar.php'; ?>
            
            <!-- User Actions -->
           

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login to Your Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot password?</a>
                    <p class="mt-2">Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Create an Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="register.php" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                        <label class="form-check-label" for="agreeTerms">I agree to the <a href="#">Terms & Conditions</a></label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>  

    <!-- Hero Carousel -->
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
            <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/slider1.jpg" class="d-block w-100" alt="Ancient Ayurveda Wisdom">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="animate__animated animate__fadeInDown">Ancient Wisdom, Modern Healing</h2>
                    <p class="animate__animated animate__fadeInUp">Discover the power of 5000-year-old Ayurvedic traditions for holistic wellness</p>
                    <a href="about.html" class="btn btn-primary mt-3 animate__animated animate__fadeInUp">Explore More</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/slider2.jpg" class="d-block w-100" alt="Organic Herbs">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="animate__animated animate__fadeInDown">100% Organic Herbs</h2>
                    <p class="animate__animated animate__fadeInUp">Sourced directly from the Himalayas for maximum potency</p>
                    <a href="shop.html" class="btn btn-primary mt-3 animate__animated animate__fadeInUp">Shop Now</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/slider3.jpg" class="d-block w-100" alt="Ayurvedic Consultation">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="animate__animated animate__fadeInDown">Personalized Ayurvedic Consultation</h2>
                    <p class="animate__animated animate__fadeInUp">Book a session with our certified Ayurvedic doctors</p>
                    <a href="consultation.html" class="btn btn-primary mt-3 animate__animated animate__fadeInUp">Book Now</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/slider4.jpg" class="d-block w-100" alt="Premium Membership">
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="animate__animated animate__fadeInDown">Premium Membership</h2>
                    <p class="animate__animated animate__fadeInUp">Get exclusive access to rare herbs and personalized wellness plans</p>
                    <a href="premium.html" class="btn btn-primary mt-3 animate__animated animate__fadeInUp">Join Now</a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Stats Section -->
    <div class="container my-5">
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number">500+</div>
                    <div class="stats-label">Ayurvedic Products</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number">25+</div>
                    <div class="stats-label">Years Experience</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number">10K+</div>
                    <div class="stats-label">Happy Customers</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-number">100%</div>
                    <div class="stats-label">Organic & Natural</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Cards Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Explore Our Offerings</h2>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card" onclick="window.location.href='ayurvedaplants.php'">
                    <div class="premium-badge">Premium</div>
                    <img src="images/aaurvedicplants.jpeg" class="card-img-top" alt="Ayurveda Plants">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ayurveda & Nutritional Plants</h5>
                        <p class="card-text">Discover our collection of rare and medicinal plants with detailed growing guides</p>
                        <a href="ayurvedaplants.php" class="btn btn-outline-primary">Explore</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card" onclick="window.location.href='products.php'">
                    <div class="premium-badge">Premium</div>
                    <img src="images/aaurvedameds.jpeg" class="card-img-top" alt="Ayurveda Medicine">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ayurveda Medicines & Drugs</h5>
                        <p class="card-text">Authentic formulations prepared by certified Ayurvedic practitioners</p>
                        <a href="products.php" class="btn btn-outline-primary">Explore</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card" onclick="window.location.href='articles.php'">
                    <img src="images/article.jpeg" class="card-img-top" alt="Articles">
                    <div class="card-body text-center">
                        <h5 class="card-title">Articles & Success Stories</h5>
                        <p class="card-text">Learn from experts and read inspiring wellness journeys</p>
                        <a href="articles.php" class="btn btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Why Choose HerbVeda Hub?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-certificate feature-icon"></i>
                    <h4>Authentic Products</h4>
                    <p>All our products are certified authentic and sourced directly from trusted growers and manufacturers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-leaf feature-icon"></i>
                    <h4>100% Organic</h4>
                    <p>No chemicals, no preservatives. Just pure, natural ingredients as nature intended.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-user-md feature-icon"></i>
                    <h4>Expert Guidance</h4>
                    <p>Get personalized recommendations from our team of Ayurvedic doctors.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Membership Section -->
    <div class="container my-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <img src="images/premium-membership.jpg" class="img-fluid rounded" alt="Premium Membership">
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4">Upgrade to Premium</h2>
                <p>Join our premium membership program to unlock exclusive benefits:</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> 15% discount on all products</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Free monthly consultations</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Access to rare herbs and formulations</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Personalized wellness plans</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Priority customer support</li>
                </ul>
                <a href="premium.html" class="btn btn-primary mt-3">Learn More</a>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">What Our Customers Say</h2>
        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="testimonial-card text-center p-4">
                                <img src="images/testimonial1.jpg" class="testimonial-img mb-3" alt="Customer">
                                <h5>Dr. Priya Sharma</h5>
                                <p class="text-muted mb-3">Ayurvedic Practitioner, Mumbai</p>
                                <p class="mb-4">"HerbVeda Hub has transformed my practice. The quality of their herbs is unmatched, and my patients have seen remarkable improvements since I started using their products."</p>
                                <div class="rating">⭐⭐⭐⭐⭐</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="testimonial-card text-center p-4">
                                <img src="images/testimonial2.jpg" class="testimonial-img mb-3" alt="Customer">
                                <h5>Rahul Kapoor</h5>
                                <p class="text-muted mb-3">Premium Member, Delhi</p>
                                <p class="mb-4">"The premium membership has been worth every penny. The personalized wellness plan helped me address my digestive issues naturally, and the monthly consultations keep me on track."</p>
                                <div class="rating">⭐⭐⭐⭐⭐</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="testimonial-card text-center p-4">
                                <img src="images/testimonial3.jpg" class="testimonial-img mb-3" alt="Customer">
                                <h5>Ananya Patel</h5>
                                <p class="text-muted mb-3">Yoga Instructor, Bangalore</p>
                                <p class="mb-4">"I've tried many Ayurvedic brands, but HerbVeda Hub stands out for their purity and effectiveness. Their Ashwagandha has helped me manage stress significantly better."</p>
                                <div class="rating">⭐⭐⭐⭐⭐</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <!-- Newsletter Section -->
    <div class="newsletter">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Join Our Wellness Community</h2>
                    <p class="mb-5">Subscribe to our newsletter for exclusive content, special offers, and Ayurvedic wisdom delivered to your inbox.</p>
                    <form class="row g-3 justify-content-center">
                        <div class="col-md-8">
                            <input type="email" class="form-control" placeholder="Your Email Address">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn w-100">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Preview Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">From Our Blog</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card">
                    <img src="images/blog1.jpg" class="card-img-top" alt="Ayurvedic Routine">
                    <div class="card-body">
                        <h5 class="card-title">Daily Ayurvedic Routine for Balance</h5>
                        <p class="card-text">Discover how a simple daily routine can transform your health according to Ayurvedic principles.</p>
                        <a href="blog-post.html" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="images/blog2.jpg" class="card-img-top" alt="Herbal Remedies">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 Herbal Remedies for Immunity</h5>
                        <p class="card-text">Boost your immune system naturally with these powerful Ayurvedic herbs.</p>
                        <a href="blog-post.html" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="images/blog3.jpg" class="card-img-top" alt="Detox Guide">
                    <div class="card-body">
                        <h5 class="card-title">Complete Guide to Ayurvedic Detox</h5>
                        <p class="card-text">Learn how to cleanse your body and mind the Ayurvedic way for renewed energy.</p>
                        <a href="blog-post.html" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="articles.php" class="btn btn-primary">View All Articles</a>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="mb-4">Have Questions?</h2>
                <p>Our Ayurvedic experts are here to help you on your wellness journey. Reach out to us with any questions or for personalized recommendations.</p>
                <div class="mt-4">
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Ayurveda Street, Nature City, India</p>
                    <p><i class="fas fa-phone me-2"></i> +91 9876543210</p>
                    <p><i class="fas fa-envelope me-2"></i> contact@herbvedahub.com</p>
                </div>
                <div class="mt-4">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="auth-form">
                    <h3 class="mb-4">Send Us a Message</h3>
                    <form>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Subject">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="4" placeholder="Your Message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Your Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="resetEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="resetEmail" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-action-btn" data-bs-toggle="modal" data-bs-target="#consultationModal">
        <i class="fas fa-calendar-check"></i>
    </div>

    <!-- Consultation Modal -->
    <div class="modal fade" id="consultationModal" tabindex="-1" aria-labelledby="consultationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consultationModalLabel">Book an Ayurvedic Consultation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Select Practitioner:</h6>
                            <div class="list-group mb-3">
                                <a href="#" class="list-group-item list-group-item-action active">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Dr. Ananya Sharma</h6>
                                        <small>⭐ 4.9 (128)</small>
                                    </div>
                                    <p class="mb-1">Specializes in Digestive Health</p>
                                    <small>Available Today</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Dr. Rajiv Patel</h6>
                                        <small>⭐ 4.8 (95)</small>
                                    </div>
                                    <p class="mb-1">Specializes in Stress Management</p>
                                    <small>Available Tomorrow</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Dr. Meera Krishnan</h6>
                                        <small>⭐ 4.9 (142)</small>
                                    </div>
                                    <p class="mb-1">Specializes in Women's Health</p>
                                    <small>Available Friday</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Select Date & Time:</h6>
                            <div class="mb-3">
                                <input type="date" class="form-control" id="consultationDate">
                            </div>
                            <div class="time-slots">
                                <h6 class="mt-3">Available Time Slots:</h6>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <button class="btn btn-outline-primary btn-sm">10:00 AM</button>
                                    <button class="btn btn-outline-primary btn-sm">11:30 AM</button>
                                    <button class="btn btn-outline-primary btn-sm">2:00 PM</button>
                                    <button class="btn btn-outline-primary btn-sm">3:30 PM</button>
                                    <button class="btn btn-outline-primary btn-sm">5:00 PM</button>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="btn btn-primary w-100">Book Consultation (₹500)</button>
                                <p class="text-center small mt-2">Premium members get 1 free consultation per month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="pt-5 pb-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="mb-4">HerbVeda Hub</h5>
                    <p>Your trusted source for authentic Ayurvedic products and wisdom since 1995.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php">Home</a></li>
                        <li class="mb-2"><a href="about.php">About Us</a></li>
                        <li class="mb-2"><a href="products.php">Products</a></li>
                        <li class="mb-2"><a href="articles.php">Blog</a></li>
                        <li class="mb-2"><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="mb-4">Customer Service</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="faq.html">FAQ</a></li>
                        <li class="mb-2"><a href="shipping.html">Shipping Policy</a></li>
                        <li class="mb-2"><a href="returns.html">Returns & Refunds</a></li>
                        <li class="mb-2"><a href="privacy.html">Privacy Policy</a></li>
                        <li class="mb-2"><a href="terms.html">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Contact Info</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Ayurveda Street, Nature City, India</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +91 9876543210</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> contact@herbvedahub.com</li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i> Mon-Sat: 9AM - 6PM</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 HerbVeda Hub. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <img src="images/payment-methods.png" alt="Payment Methods" class="img-fluid" style="max-height: 30px;">
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" style="display: none; position: fixed; bottom: 80px; right: 30px; z-index: 99; width: 40px; height: 40px; line-height: 40px; text-align: center; background: var(--primary-color); color: white; border-radius: 50%;">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Back to top button
        window.addEventListener('scroll', function() {
            var backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        document.querySelector('.back-to-top').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // User dropdown
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user is logged in (this would be replaced with actual authentication check)
            const isLoggedIn = false; // Change to true to see logged in state
            
            const userDropdown = document.getElementById('userDropdown');
            const dropdownMenu = userDropdown.nextElementSibling;
            
            if (isLoggedIn) {
                dropdownMenu.innerHTML = `
                    <li><a class="dropdown-item" href="profile.html"><i class="fas fa-user me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="orders.html"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                    <li><a class="dropdown-item" href="consultations.html"><i class="fas fa-calendar-check me-2"></i>My Consultations</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                `;
                userDropdown.innerHTML = '<i class="fas fa-user-circle"></i> <span class="d-none d-md-inline">Welcome, User</span>';
            }
        });
    </script>
</body>
</html>