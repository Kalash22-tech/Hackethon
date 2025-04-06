<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'dbconnect.php';

// Check if user is logged in
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

// Check if user is premium
if (!function_exists('isPremiumUser')) {
    function isPremiumUser() {
        return isLoggedIn() && isset($_SESSION['is_premium']) && $_SESSION['is_premium'] == 1;
    }
}

// Login function
if (!function_exists('loginUser')) {
    function loginUser($email, $password) {
        global $conn;
        
        $stmt = $conn->prepare("SELECT id, name, password, is_premium FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $email;
                $_SESSION['is_premium'] = $user['is_premium'];
                return true;
            }
        }
        return false;
    }
}

// Registration function
if (!function_exists('registerUser')) {
    function registerUser($name, $email, $password) {
        global $conn;
        
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}

// Get user wishlist
if (!function_exists('getUserWishlist')) {
    function getUserWishlist($user_id) {
        global $conn;
        $stmt = $conn->prepare("SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}

// Get user cart
if (!function_exists('getUserCart')) {
    function getUserCart($user_id) {
        global $conn;
        $stmt = $conn->prepare("SELECT p.*, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}

// Logout function
if (!function_exists('logoutUser')) {
    function logoutUser() {
        session_unset();
        session_destroy();
    }
}
?>