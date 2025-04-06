<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayurvedic Products | Premium Herbal Solutions</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4a6b3b;
            --secondary-color: #d4a762;
            --dark-color: #1a2e1c;
            --light-color: #f8f5f0;
            --accent-color: #8c3a2b;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
        }
        
        .navbar {
            background-color: var(--dark-color) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            padding: 10px 0;
            background-color: rgba(26, 46, 28, 0.95) !important;
        }
        
        .navbar-brand img {
            height: 50px;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled .navbar-brand img {
            height: 40px;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 10px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--secondary-color);
            bottom: 0;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover:after {
            width: 100%;
        }
        
        .carousel {
            margin-top: 76px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .carousel-item {
            height: 70vh;
            min-height: 400px;
        }
        
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            filter: brightness(0.7);
        }
        
        .carousel-caption {
            bottom: 30%;
            text-align: left;
            padding: 20px;
            background: rgba(74, 107, 59, 0.7);
            border-radius: 5px;
            animation: fadeInUp 1s ease;
        }
        
        .carousel-caption h5 {
            font-size: 2.5rem;
            color: white;
            text-shadow: none;
        }
        
        .carousel-caption p {
            font-size: 1.2rem;
            color: #f8f8f8;
        }
        
        .sidebar {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .filter-section h4 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .filter-btn {
            display: block;
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 10px;
            text-align: left;
            background: white;
            border: 1px solid #eee;
            border-radius: 5px;
            color: var(--dark-color);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .filter-btn i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .filter-btn:hover i, .filter-btn.active i {
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
            cursor: pointer;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: all 0.5s ease;
        }
        
        .card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .card-body {
            padding: 20px;
            background: white;
        }
        
        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .card-text {
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-color);
            border-color: var(--dark-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        #view-more-btn {
            display: block;
            margin: 40px auto;
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        #view-more-btn:hover {
            background-color: #c09555;
            border-color: #c09555;
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 1;
        }
        
        footer {
            background: var(--dark-color);
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }
        
        .footer-logo img {
            height: 60px;
            margin-bottom: 20px;
        }
        
        .footer-links h5 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--secondary-color);
            padding-left: 5px;
        }
        
        .social-icons a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            background: var(--secondary-color);
            transform: translateY(-5px);
        }
        
        .copyright {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 30px;
            font-size: 0.9rem;
            color: #aaa;
        }
        
        @media (max-width: 768px) {
            .carousel-item {
                height: 50vh;
            }
            
            .carousel-caption {
                bottom: 20%;
            }
            
            .carousel-caption h5 {
                font-size: 1.5rem;
            }
            
            .carousel-caption p {
                font-size: 1rem;
            }
            
            .sidebar {
                margin-bottom: 30px;
                position: static;
            }
        }
        
        /* Animation classes */
        .animate-delay-1 {
            animation-delay: 0.2s;
        }
        
        .animate-delay-2 {
            animation-delay: 0.4s;
        }
        
        .animate-delay-3 {
            animation-delay: 0.6s;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/logo.jpeg" alt="Ayurvedic Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Shop Now</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-shopping-cart"></i></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Product Slideshow -->
    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" class="d-block w-100" alt="Ayurvedic Herbs">
                <div class="carousel-caption animate__animated animate__fadeInUp">
                    <h5>Natural Herbs for a Healthy Life</h5>
                    <p>Discover the ancient wisdom of Ayurveda with our premium herbal products, crafted for modern wellness.</p>
                    <a href="#" class="btn btn-primary mt-3">Explore Products</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1595341595379-cf0ff033ce8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" class="d-block w-100" alt="Hair Care">
                <div class="carousel-caption animate__animated animate__fadeInUp">
                    <h5>Ayurvedic Hair & Skin Care</h5>
                    <p>Nourish your body with nature's best ingredients for radiant skin and lustrous hair.</p>
                    <a href="#" class="btn btn-primary mt-3">Shop Now</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1519735777090-ec97162dc266?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" class="d-block w-100" alt="Immunity Boosters">
                <div class="carousel-caption animate__animated animate__fadeInUp">
                    <h5>Boost Immunity Naturally</h5>
                    <p>Strengthen your body's defenses with our time-tested Ayurvedic formulations.</p>
                    <a href="#" class="btn btn-primary mt-3">Learn More</a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Filter Section (Sidebar) -->
            <aside class="col-md-3 sidebar animate__animated animate__fadeInLeft">
                <div class="filter-section">
                    <h4><i class="fas fa-filter me-2"></i>Filter Products</h4>
                    <button class="filter-btn active" data-category="all">
                        <i class="fas fa-seedling"></i> All Products
                    </button>
                    <button class="filter-btn" data-category="hair-care">
                        <i class="fas fa-spa"></i> Hair Care
                    </button>
                    <button class="filter-btn" data-category="skin-care">
                        <i class="fas fa-brain"></i> Skin Care
                    </button>
                    <button class="filter-btn" data-category="digestive-health">
                        <i class="fas fa-leaf"></i> Digestive Health
                    </button>
                    <button class="filter-btn" data-category="immunity-boosters">
                        <i class="fas fa-shield-virus"></i> Immunity Boosters
                    </button>
                    <button class="filter-btn" data-category="stress-relief">
                        <i class="fas fa-heart"></i> Stress Relief
                    </button>
                    <button class="filter-btn" data-category="liver-care">
                        <i class="fas fa-liver"></i> Liver Care
                    </button>
                    
                    <div class="price-filter mt-4">
                        <h5 class="mb-3">Price Range</h5>
                        <div class="range-slider">
                            <input type="range" class="form-range" min="0" max="5000" step="100" id="priceRange">
                            <div class="d-flex justify-content-between">
                                <span>₹0</span>
                                <span>₹5000</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rating-filter mt-4">
                        <h5 class="mb-3">Customer Rating</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rating5">
                            <label class="form-check-label" for="rating5">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rating4">
                            <label class="form-check-label" for="rating4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="far fa-star text-warning"></i> & up
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rating3">
                            <label class="form-check-label" for="rating3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="far fa-star text-warning"></i>
                                <i class="far fa-star text-warning"></i> & up
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Product Cards -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="animate__animated animate__fadeIn">Our Premium Products</h2>
                    <div class="sort-options">
                        <select class="form-select" id="sortProducts">
                            <option selected>Sort By</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="rating">Customer Rating</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>
                </div>
                
                <div class="row" id="product-container">
                    <!-- Products will be dynamically inserted here -->
                </div>
                
                <!-- View More Button -->
                <button id="view-more-btn" class="btn btn-primary mt-4 animate__animated animate__fadeInUp">View More Products</button>
            </div>
        </div>
    </div>

    <!-- Featured Categories Section -->
    <section class="featured-categories py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Shop by Category</h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="category-card text-center p-4 rounded shadow-sm bg-white">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="fas fa-spa fa-3x text-primary"></i>
                        </div>
                        <h4>Hair Care</h4>
                        <p class="text-muted">Natural solutions for healthy hair</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Shop Now</a>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="category-card text-center p-4 rounded shadow-sm bg-white">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="fas fa-brain fa-3x text-primary"></i>
                        </div>
                        <h4>Skin Care</h4>
                        <p class="text-muted">Glow with nature's touch</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Shop Now</a>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="category-card text-center p-4 rounded shadow-sm bg-white">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="fas fa-leaf fa-3x text-primary"></i>
                        </div>
                        <h4>Digestive Health</h4>
                        <p class="text-muted">Balance your gut naturally</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Shop Now</a>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="category-card text-center p-4 rounded shadow-sm bg-white">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="fas fa-shield-virus fa-3x text-primary"></i>
                        </div>
                        <h4>Immunity Boosters</h4>
                        <p class="text-muted">Strengthen your defenses</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials py-5">
        <div class="container">
            <h2 class="text-center mb-5">What Our Customers Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4 rounded shadow-sm bg-white h-100">
                        <div class="rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"The hair oil transformed my dry, damaged hair in just a month. Now I have the longest, healthiest hair I've ever had!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://randomuser.me/api/portraits/women/32.jpg" class="rounded-circle me-3" width="50" alt="Customer">
                            <div>
                                <h6 class="mb-0">Priya Sharma</h6>
                                <small class="text-muted">Mumbai, India</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4 rounded shadow-sm bg-white h-100">
                        <div class="rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="mb-4">"After trying countless products for my skin issues, this Ayurvedic cream finally gave me relief. My skin has never been better!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://randomuser.me/api/portraits/men/45.jpg" class="rounded-circle me-3" width="50" alt="Customer">
                            <div>
                                <h6 class="mb-0">Rahul Patel</h6>
                                <small class="text-muted">Delhi, India</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4 rounded shadow-sm bg-white h-100">
                        <div class="rating mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"The immunity booster kept my family healthy throughout the season when everyone else was falling sick. Highly recommended!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" class="rounded-circle me-3" width="50" alt="Customer">
                            <div>
                                <h6 class="mb-0">Ananya Reddy</h6>
                                <small class="text-muted">Bangalore, India</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h3 class="mb-2">Join Our Wellness Community</h3>
                    <p class="mb-0">Subscribe to receive Ayurvedic tips, exclusive offers, and product updates.</p>
                </div>
                <div class="col-md-6">
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-light text-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-logo">
                        <img src="https://via.placeholder.com/150x50?text=AyurVeda" alt="Logo">
                    </div>
                    <p>Bringing the ancient wisdom of Ayurveda to modern life with authentic, high-quality herbal products.</p>
                    <div class="social-icons mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <div class="footer-links">
                        <h5>Quick Links</h5>
                        <ul>
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Products</a></li>
                            <li><a href="#">Shop</a></li>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <div class="footer-links">
                        <h5>Categories</h5>
                        <ul>
                            <li><a href="#">Hair Care</a></li>
                            <li><a href="#">Skin Care</a></li>
                            <li><a href="#">Digestive Health</a></li>
                            <li><a href="#">Immunity Boosters</a></li>
                            <li><a href="#">All Products</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-4">
                    <div class="footer-links">
                        <h5>Contact Us</h5>
                        <ul class="contact-info">
                            <li><i class="fas fa-map-marker-alt me-2"></i> 123 Ayurveda Lane, Rishikesh, India</li>
                            <li><i class="fas fa-phone me-2"></i> +91 9876543210</li>
                            <li><i class="fas fa-envelope me-2"></i> info@ayurvedaproducts.com</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="copyright text-center">
                <p>&copy; 2023 Ayurvedic Products. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Product data with more details
        const products = [
            { 
                id: 1, 
                name: 'Kashisadi Tel', 
                category: 'skin-care', 
                description: 'Used for treating skin infections, wounds, and ulcers with natural herbs.', 
                price: 349, 
                rating: 4.5,
                image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
                badge: 'BESTSELLER'
            },
            { 
                id: 2, 
                name: 'Triphala Tablets', 
                category: 'digestive-health', 
                description: 'Supports digestion and detoxification with three powerful fruits.', 
                price: 199, 
                rating: 4.2,
                image: 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
                badge: 'NEW'
            },
            { 
                id: 3, 
                name: 'Shivotone', 
                category: 'general-health', 
                description: 'Herbal tonic for improving strength, digestion, and immunity.', 
                price: 249, 
                rating: 4.0,
                image: 'https://images.unsplash.com/photo-1519735777090-ec97162dc266?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 4, 
                name: 'Xerfer Capsules', 
                category: 'iron-supplement', 
                description: 'Boosts hemoglobin levels and reduces fatigue naturally.', 
                price: 299, 
                rating: 4.7,
                image: 'https://images.unsplash.com/photo-1595341595379-cf0ff033ce8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
                badge: 'BESTSELLER'
            },
            { 
                id: 5, 
                name: 'Indulekha Bringha Oil', 
                category: 'hair-care', 
                description: 'Prevents hair fall and promotes hair growth with Ayurvedic herbs.', 
                price: 399, 
                rating: 4.8,
                image: 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
                badge: 'TRENDING'
            },
            { 
                id: 6, 
                name: 'Vilba Cream', 
                category: 'skin-care', 
                description: 'Helps treat eczema, psoriasis, and skin irritation effectively.', 
                price: 279, 
                rating: 4.3,
                image: 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 7, 
                name: 'Patanjali Aloe Vera Juice', 
                category: 'immunity-boosters', 
                description: 'Supports digestion, skin, and hair health with pure Aloe Vera.', 
                price: 179, 
                rating: 4.1,
                image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 8, 
                name: 'Himalaya Liv.52 Tablets', 
                category: 'liver-care', 
                description: 'Promotes liver detoxification and improves digestion naturally.', 
                price: 229, 
                rating: 4.6,
                image: 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
                badge: 'BESTSELLER'
            },
            { 
                id: 9, 
                name: 'Dabur Chyawanprash', 
                category: 'immunity-boosters', 
                description: 'Rich in Vitamin C, enhances immunity and energy levels.', 
                price: 349, 
                rating: 4.4,
                image: 'https://images.unsplash.com/photo-1519735777090-ec97162dc266?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 10, 
                name: 'Zandu Balm', 
                category: 'pain-relief', 
                description: 'Provides quick relief from headaches and body pain with herbs.', 
                price: 99, 
                rating: 4.0,
                image: 'https://images.unsplash.com/photo-1595341595379-cf0ff033ce8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 11, 
                name: 'Baidyanath Bhringraj Oil', 
                category: 'hair-care', 
                description: 'Strengthens hair roots and prevents premature graying.', 
                price: 259, 
                rating: 4.5,
                image: 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 12, 
                name: 'Himalaya Ashwagandha Capsules', 
                category: 'stress-relief', 
                description: 'Reduces stress, improves stamina, and boosts energy naturally.', 
                price: 299, 
                rating: 4.7,
                image: 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
                badge: 'TRENDING'
            },
            { 
                id: 13, 
                name: 'Kesh King Oil', 
                category: 'hair-care', 
                description: 'Prevents hair fall and promotes thick, long hair growth.', 
                price: 379, 
                rating: 4.3,
                image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 14, 
                name: 'Patanjali Divya Medha Kwath', 
                category: 'brain-health', 
                description: 'Improves memory and concentration with natural herbs.', 
                price: 199, 
                rating: 4.1,
                image: 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            },
            { 
                id: 15, 
                name: 'Dabur Lal Tail', 
                category: 'baby-care', 
                description: 'Ayurvedic massage oil for babies to strengthen muscles.', 
                price: 149, 
                rating: 4.6,
                image: 'https://images.unsplash.com/photo-1519735777090-ec97162dc266?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
                badge: 'BESTSELLER'
            },
            { 
                id: 16, 
                name: 'Himalaya Pure Hands Sanitizer', 
                category: 'hygiene', 
                description: 'Alcohol-based sanitizer with natural ingredients.', 
                price: 129, 
                rating: 4.2,
                image: 'https://images.unsplash.com/photo-1595341595379-cf0ff033ce8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            }
        ];

        let visibleProducts = 8;
        const productContainer = document.getElementById('product-container');
        const viewMoreBtn = document.getElementById('view-more-btn');
        const sortSelect = document.getElementById('sortProducts');
        let currentCategory = 'all';
        let currentSort = 'default';

        // Render products with animations
        function renderProducts(category = 'all', sort = 'default') {
            productContainer.innerHTML = '';
            currentCategory = category;
            currentSort = sort;
            
            // Filter products by category
            let filteredProducts = category === 'all' 
                ? [...products] 
                : products.filter(product => product.category.toLowerCase() === category.toLowerCase());
            
            // Sort products
            if (sort === 'price-low') {
                filteredProducts.sort((a, b) => a.price - b.price);
            } else if (sort === 'price-high') {
                filteredProducts.sort((a, b) => b.price - a.price);
            } else if (sort === 'rating') {
                filteredProducts.sort((a, b) => b.rating - a.rating);
            } else if (sort === 'popular') {
                // For demo, we'll consider products with badges as popular
                filteredProducts.sort((a, b) => {
                    const aPopular = a.badge ? 1 : 0;
                    const bPopular = b.badge ? 1 : 0;
                    return bPopular - aPopular || b.rating - a.rating;
                });
            }
            
            // Display products with staggered animations
            filteredProducts.slice(0, visibleProducts).forEach((product, index) => {
                const animationDelay = index % 4 * 0.2;
                const badge = product.badge 
                    ? `<span class="product-badge ${product.badge.toLowerCase()}">${product.badge}</span>` 
                    : '';
                
                const card = `
                    <div class="col-lg-3 col-md-4 col-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: ${animationDelay}s">
                        <div class="card h-100" onclick="window.location.href='product-details.html?id=${product.id}'">
                            ${badge}
                            <img src="${product.image}" class="card-img-top" alt="${product.name}">
                            <div class="card-body">
                                <h5 class="card-title">${product.name}</h5>
                                <div class="rating mb-2">
                                    ${renderStars(product.rating)}
                                    <small class="text-muted ms-1">(${product.rating})</small>
                                </div>
                                <p class="card-text">${product.description}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-primary mb-0">₹${product.price}</h5>
                                    <button class="btn btn-sm btn-outline-primary">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                productContainer.innerHTML += card;
            });
            
            // Show/hide view more button
            viewMoreBtn.style.display = filteredProducts.length > visibleProducts ? 'block' : 'none';
            
            // Add animation to view more button
            viewMoreBtn.classList.add('animate__animated', 'animate__fadeIn');
        }
        
        // Helper function to render star ratings
        function renderStars(rating) {
            let stars = '';
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 >= 0.5;
            
            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    stars += '<i class="fas fa-star text-warning"></i>';
                } else if (i === fullStars + 1 && hasHalfStar) {
                    stars += '<i class="fas fa-star-half-alt text-warning"></i>';
                } else {
                    stars += '<i class="far fa-star text-warning"></i>';
                }
            }
            
            return stars;
        }
        
        // Filter button event listeners
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Get selected category
                const selectedCategory = button.dataset.category;
                
                // Render products with current sort
                renderProducts(selectedCategory, currentSort);
            });
        });
        
        // Sort select event listener
        sortSelect.addEventListener('change', () => {
            const selectedSort = sortSelect.value;
            renderProducts(currentCategory, selectedSort);
        });
        
        // View more button event listener
        viewMoreBtn.addEventListener('click', () => {
            visibleProducts += 8;
            renderProducts(currentCategory, currentSort);
            
            // Smooth scroll to bottom of new products
            setTimeout(() => {
                window.scrollTo({
                    top: viewMoreBtn.offsetTop - 100,
                    behavior: 'smooth'
                });
            }, 300);
        });
        
        // Initialize
        function renderProducts(category = 'all', sort = 'default') {
    productContainer.innerHTML = '';
    currentCategory = category;
    currentSort = sort;
    
    // Filter products by category
    let filteredProducts = category === 'all' 
        ? [...products] 
        : products.filter(product => product.category.toLowerCase() === category.toLowerCase());
    
    // Sort products
    if (sort === 'price-low') {
        filteredProducts.sort((a, b) => a.price - b.price);
    } else if (sort === 'price-high') {
        filteredProducts.sort((a, b) => b.price - a.price);
    } else if (sort === 'rating') {
        filteredProducts.sort((a, b) => b.rating - a.rating);
    } else if (sort === 'popular') {
        // For demo, we'll consider products with badges as popular
        filteredProducts.sort((a, b) => {
            const aPopular = a.badge ? 1 : 0;
            const bPopular = b.badge ? 1 : 0;
            return bPopular - aPopular || b.rating - a.rating;
        });
    }
    
    // Display products with your template
    filteredProducts.slice(0, visibleProducts).forEach((product, index) => {
        const card = `
            <div class="col-md-3 mb-4">
                <div class="card" onclick="window.location.href='http://localhost/Hackethon/productview.php?id=${product.id}'">
                    <div class="card-body">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text">${product.description}</p>
                    </div>
                </div>
            </div>
        `;
        productContainer.innerHTML += card;
    });
    
    // Show/hide view more button
    viewMoreBtn.style.display = filteredProducts.length > visibleProducts ? 'block' : 'none';
}
        
        // Run once on page load
        animateOnScroll();
        
        // Run on scroll
        window.addEventListener('scroll', animateOnScroll);
    </script>
</body>
</html>