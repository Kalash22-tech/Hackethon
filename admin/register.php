<?php
session_start();
require_once __DIR__ . '/../dbconnect.php';

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Initialize variables
$errors = [];
$success = '';
$formData = [
    'username' => '',
    'shop_name' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token';
        http_response_code(403);
    } else {
        // Sanitize and validate input
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        $shop_name = trim($_POST['shop_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Store sanitized data for repopulation
        $formData = [
            'username' => htmlspecialchars($username),
            'shop_name' => htmlspecialchars($shop_name),
            'email' => htmlspecialchars($email),
            'phone' => htmlspecialchars($phone),
            'address' => htmlspecialchars($address)
        ];

        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 4) {
            $errors[] = 'Username must be at least 4 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers and underscores';
        }

        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter and one number';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }

        if (empty($shop_name)) {
            $errors[] = 'Shop name is required';
        } elseif (strlen($shop_name) > 100) {
            $errors[] = 'Shop name is too long';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif (strlen($email) > 255) {
            $errors[] = 'Email is too long';
        }

        if (empty($phone)) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[\d\s\-+()]{10,20}$/', $phone)) {
            $errors[] = 'Invalid phone number format';
        }

        if (empty($address)) {
            $errors[] = 'Address is required';
        } elseif (strlen($address) > 255) {
            $errors[] = 'Address is too long';
        }

        // Check if username or email exists
        if (empty($errors)) {
            $check_sql = "SELECT id FROM shop_admin WHERE username = ? OR email = ? LIMIT 1";
            $check_stmt = $conn->prepare($check_sql);
            
            if ($check_stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                $errors[] = "Registration error. Please try again.";
            } else {
                $check_stmt->bind_param("ss", $username, $email);
                $check_stmt->execute();
                $check_stmt->store_result();
                
                if ($check_stmt->num_rows > 0) {
                    $errors[] = 'Username or email already exists';
                }
                $check_stmt->close();
            }
        }

        // Process registration if no errors
        if (empty($errors)) {
            // Hash password with cost factor of 12
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Insert new shop admin using prepared statement
            $insert_sql = "INSERT INTO shop_admin 
                          (username, password, shop_name, email, phone, address, created_at) 
                          VALUES 
                          (?, ?, ?, ?, ?, ?, NOW())";
            
            $insert_stmt = $conn->prepare($insert_sql);
            
            if ($insert_stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                $errors[] = "Registration error. Please try again.";
            } else {
                $insert_stmt->bind_param("ssssss", $username, $hashed_password, $shop_name, $email, $phone, $address);
                
                if ($insert_stmt->execute()) {
                    $success = 'Registration successful! You can now <a href="login.php">login</a>.';
                    $formData = []; // Clear form data
                    
                    // Regenerate session ID after successful registration
                    session_regenerate_id(true);
                } else {
                    error_log("Execute failed: " . $insert_stmt->error);
                    $errors[] = 'Registration failed. Please try again.';
                }
                $insert_stmt->close();
            }
        }
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Shopkeeper registration page">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:;">
    <title>Shopkeeper Registration | Primum</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/custom.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-color: #5a5c69;
        }
        
        body { 
            background-color: var(--secondary-color); 
            min-height: 100vh;
            display: flex; 
            align-items: center; 
            padding: 2rem 0;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-color);
        }
        
        .register-container { 
            max-width: 640px; 
            width: 100%;
            margin: 0 auto; 
            padding: 2.5rem; 
            background: #fff; 
            border-radius: 0.5rem; 
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); 
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        
        .register-header { 
            text-align: center; 
            margin-bottom: 2rem; 
        }
        
        .register-header h2 {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .register-header p {
            color: #858796;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .alert {
            border-radius: 0.35rem;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 0.25rem;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .register-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h2>Shopkeeper Registration</h2>
                <p>Create your admin account to manage your shop</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" autocomplete="off" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username*</label>
                        <input type="text" name="username" id="username" class="form-control" 
                               value="<?php echo $formData['username'] ?? ''; ?>" 
                               required minlength="4" maxlength="30"
                               pattern="[a-zA-Z0-9_]+" title="Letters, numbers and underscores only">
                        <div class="invalid-feedback">Please choose a valid username (4-30 chars, letters, numbers, underscores)</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="shop_name" class="form-label">Shop Name*</label>
                        <input type="text" name="shop_name" id="shop_name" class="form-control" 
                               value="<?php echo $formData['shop_name'] ?? ''; ?>" 
                               required maxlength="100">
                        <div class="invalid-feedback">Please enter your shop name</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password*</label>
                        <input type="password" name="password" id="password" class="form-control" 
                               required minlength="8" aria-describedby="passwordHelp">
                        <div id="passwordHelp" class="form-text">Minimum 8 characters with uppercase, lowercase and number</div>
                        <div class="password-strength mt-2">
                            <div class="password-strength-bar" id="password-strength-bar"></div>
                        </div>
                        <div class="invalid-feedback">Please provide a valid password</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password*</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                               required minlength="8">
                        <div class="invalid-feedback">Passwords must match</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" name="email" id="email" class="form-control" 
                               value="<?php echo $formData['email'] ?? ''; ?>" 
                               required maxlength="255">
                        <div class="invalid-feedback">Please provide a valid email</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone*</label>
                        <input type="tel" name="phone" id="phone" class="form-control" 
                               value="<?php echo $formData['phone'] ?? ''; ?>" 
                               required pattern="[\d\s\-+()]{10,20}">
                        <div class="invalid-feedback">Please provide a valid phone number</div>
                    </div>
                    
                    <div class="col-12">
                        <label for="address" class="form-label">Shop Address*</label>
                        <textarea name="address" id="address" class="form-control" rows="3" 
                                  required maxlength="255"><?php 
                            echo $formData['address'] ?? ''; 
                        ?></textarea>
                        <div class="invalid-feedback">Please provide your shop address</div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                            </label>
                            <div class="invalid-feedback">You must agree before submitting</div>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
                    </div>
                    
                    <div class="col-12 text-center mt-3">
                        <p>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please read these terms and conditions carefully before registering as a shopkeeper.</p>
                    <!-- Add your actual terms here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        // Client-side validation and password strength meter
        (function() {
            'use strict';
            
            // Fetch form and inputs
            const form = document.querySelector('form');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const strengthBar = document.getElementById('password-strength-bar');
            
            // Password strength calculation
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                
                // Character variety
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[a-z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                
                // Update strength bar
                const width = (strength / 5) * 100;
                strengthBar.style.width = width + '%';
                
                // Update color
                if (strength <= 2) {
                    strengthBar.style.backgroundColor = '#dc3545'; // Red
                } else if (strength <= 3) {
                    strengthBar.style.backgroundColor = '#fd7e14'; // Orange
                } else if (strength <= 4) {
                    strengthBar.style.backgroundColor = '#ffc107'; // Yellow
                } else {
                    strengthBar.style.backgroundColor = '#28a745'; // Green
                }
            });
            
            // Confirm password validation
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            // Form validation
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
            
            // Enable tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        })();
    </script>
</body>
</html>