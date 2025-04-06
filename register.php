<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Combine first and last name
    $fullName = $firstName . ' ' . $lastName;

    // Validate inputs
    $errors = [];
    
    if (empty($firstName) || empty($lastName)) {
        $errors[] = "First name and last name are required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($_POST['agreeTerms'])) {
        $errors[] = "You must agree to the terms and conditions";
    }

    // Check if email exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already in use";
        }
        $stmt->close();
    }

    // Register user if no errors
    if (empty($errors)) {
        if (registerUser($fullName, $email, $password)) {
            // Auto-login after registration
            loginUser($email, $password);
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HerbVeda Wellness</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4a6b3a;
            --secondary: #d4a76a;
            --light: #f8f5f0;
            --dark: #2a3a1e;
            --accent: #a84448;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            background-image: url('https://images.unsplash.com/photo-1606787366850-de6330128bfc?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(248, 245, 240, 0.85);
            z-index: 0;
        }
        
        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(42, 58, 30, 0.15);
            overflow: hidden;
            margin: 2rem;
        }
        
        .register-header {
            background-color: var(--primary);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .register-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .register-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            width: 100%;
            height: 40px;
            background-color: var(--primary);
            clip-path: ellipse(50% 50% at 50% 50%);
            z-index: -1;
        }
        
        .register-form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(212, 167, 106, 0.2);
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 38px;
            color: var(--primary);
        }
        
        .name-fields {
            display: flex;
            gap: 1rem;
        }
        
        .name-fields .form-group {
            flex: 1;
        }
        
        .name-fields .form-control {
            padding-left: 15px;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            background-color: var(--dark);
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-danger ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .terms-check {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .terms-check input {
            margin-right: 10px;
            accent-color: var(--primary);
        }
        
        .terms-check label {
            font-size: 0.9rem;
        }
        
        .terms-check a {
            color: var(--primary);
            font-weight: 600;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        
        .login-link a {
            color: var(--primary);
            font-weight: 600;
        }
        
        .ayurvedic-elements {
            display: flex;
            justify-content: center;
            margin: 1.5rem 0;
        }
        
        .element {
            margin: 0 10px;
            text-align: center;
        }
        
        .element i {
            font-size: 1.5rem;
            color: var(--secondary);
            margin-bottom: 5px;
        }
        
        .element span {
            display: block;
            font-size: 0.7rem;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        @media (max-width: 576px) {
            .register-container {
                margin: 1rem;
            }
            
            .register-form {
                padding: 1.5rem;
            }
            
            .name-fields {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Join HerbVeda Wellness</h1>
            <p>Begin your journey to holistic health</p>
        </div>
        
        <div class="register-form">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="name-fields">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" placeholder="First name" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Last name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Create password (min 8 characters)" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                
                <div class="terms-check">
                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                    <label for="agreeTerms">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn">Create Account</button>
            </form>
            
            <div class="ayurvedic-elements">
                <div class="element">
                    <i class="fas fa-leaf"></i>
                    <span>Vata</span>
                </div>
                <div class="element">
                    <i class="fas fa-fire"></i>
                    <span>Pitta</span>
                </div>
                <div class="element">
                    <i class="fas fa-water"></i>
                    <span>Kapha</span>
                </div>
            </div>
            
            <div class="login-link">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</body>
</html>