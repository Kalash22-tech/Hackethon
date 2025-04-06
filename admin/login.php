<?php
session_start();
require_once __DIR__ . '/../dbconnect.php';

// Enable strict error reporting for security
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable in production, log instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data:;");

// Initialize variables
$error = '';
$login_attempts = 0;
$lockout_time = 0;

// Check for existing login attempts
if (isset($_SESSION['login_attempts'])) {
    $login_attempts = $_SESSION['login_attempts'];
    $last_attempt = $_SESSION['last_attempt_time'] ?? 0;
    
    // Implement account lockout after 5 failed attempts (30 minute lockout)
    if ($login_attempts >= 5 && (time() - $last_attempt) < 1800) {
        $lockout_time = 1800 - (time() - $last_attempt);
        $error = "Too many failed attempts. Please try again in " . gmdate("i:s", $lockout_time) . " minutes.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $lockout_time == 0) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = $conn->real_escape_string(trim($_POST['username']));
        $password = trim($_POST['password']);
        
        // Input validation
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } elseif (strlen($username) > 50 || strlen($password) > 255) {
            $error = 'Invalid input length';
        } else {
            // Prepare SQL statement with additional security checks
            $sql = "SELECT id, username, password, shop_name, is_active, last_login, failed_attempts 
                    FROM shop_admin 
                    WHERE username = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                error_log("Database error in login preparation: " . $conn->error);
                $error = "System error. Please try again later.";
            } else {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 1) {
                    $admin = $result->fetch_assoc();
                    
                    // Check account lock status in database
                    if ($admin['failed_attempts'] >= 5) {
                        $error = 'Account locked due to too many failed attempts. Please contact support.';
                    } elseif (password_verify($password, $admin['password'])) {
                        if ($admin['is_active'] == 1) {
                            // Reset failed attempts on successful login
                            $reset_sql = "UPDATE shop_admin SET failed_attempts = 0, last_login = NOW() WHERE id = ?";
                            $reset_stmt = $conn->prepare($reset_sql);
                            $reset_stmt->bind_param("i", $admin['id']);
                            $reset_stmt->execute();
                            $reset_stmt->close();
                            
                            // Regenerate session ID to prevent fixation
                            session_regenerate_id(true);
                            
                            // Set secure session variables
                            $_SESSION['shop_admin_id'] = $admin['id'];
                            $_SESSION['shop_admin_username'] = $admin['username'];
                            $_SESSION['shop_admin_shop'] = $admin['shop_name'];
                            $_SESSION['logged_in'] = true;
                            $_SESSION['last_activity'] = time();
                            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                            
                            // Reset login attempts
                            unset($_SESSION['login_attempts']);
                            unset($_SESSION['last_attempt_time']);
                            
                            // Set secure cookie parameters
                            setcookie(session_name(), session_id(), [
                                'expires' => time() + 86400, // 1 day
                                'path' => '/',
                                'domain' => $_SERVER['HTTP_HOST'],
                                'secure' => true,
                                'httponly' => true,
                                'samesite' => 'Strict'
                            ]);
                            
                            // Log successful login
                            error_log("Successful login for user: " . $username);
                            
                            // Redirect to dashboard
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $error = 'Your account is inactive. Please contact support.';
                        }
                    } else {
                        // Increment failed attempts in database
                        $update_sql = "UPDATE shop_admin SET failed_attempts = failed_attempts + 1 WHERE username = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("s", $username);
                        $update_stmt->execute();
                        $update_stmt->close();
                        
                        // Track failed attempts in session
                        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                        $_SESSION['last_attempt_time'] = time();
                        
                        $error = 'Invalid username or password';
                        error_log("Failed login attempt for user: " . $username);
                    }
                } else {
                    // Username doesn't exist - generic error message for security
                    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                    $_SESSION['last_attempt_time'] = time();
                    
                    $error = 'Invalid username or password';
                }
                $stmt->close();
            }
        }
    }
}

// Generate new CSRF token for each request
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Shopkeeper Admin Login">
    <title>Shopkeeper Login | Premium Admin Portal</title>
    
    <!-- Preload critical assets -->
    <link rel="preload" href="../css/bootstrap.min.css" as="style">
    <link rel="preload" href="../js/bootstrap.bundle.min.js" as="script">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="../assets/favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --danger-color: #f72585;
            --success-color: #4cc9f0;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .login-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h2 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .alert {
            margin-bottom: 25px;
            border-radius: 8px;
            padding: 15px;
        }
        
        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding-left: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            height: 50px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .login-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .password-container {
            position: relative;
        }
        
        .lockout-message {
            color: var(--danger-color);
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .brand-logo {
            max-height: 50px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
                margin: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <img src="../assets/logo.png" alt="Company Logo" class="brand-logo">
                <h2>Shopkeeper Portal</h2>
                <p class="text-muted">Access your premium admin dashboard</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($lockout_time > 0): ?>
                <div class="lockout-message">
                    <i class="fas fa-lock me-2"></i>
                    Account temporarily locked. Please try again later.
                </div>
            <?php else: ?>
                <form method="POST" action="" id="loginForm" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required
                                   placeholder="Enter your username" autocomplete="username">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-container">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required
                                       placeholder="Enter your password" autocomplete="current-password">
                            </div>
                            <span class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <div>
                            <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="loginButton">
                            <span id="buttonText">Login</span>
                            <span id="buttonSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                    
                    <div class="login-footer">
                        <p>Don't have an account? <a href="register.php">Request access</a></p>
                        <p class="mt-2 text-muted">&copy; <?php echo date('Y'); ?> Your Company. All rights reserved.</p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="../js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
        
        // Form submission handler
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const spinner = document.getElementById('buttonSpinner');
            
            button.disabled = true;
            buttonText.textContent = 'Authenticating...';
            spinner.classList.remove('d-none');
        });
        
        // Input validation
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });
        
        // Focus first input on page load
        window.addEventListener('DOMContentLoaded', () => {
            const username = document.getElementById('username');
            if (username) username.focus();
        });
    </script>
</body>
</html>