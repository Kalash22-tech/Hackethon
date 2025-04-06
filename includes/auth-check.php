<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Enhanced session security settings - should be set before session_start()
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    
    if (!empty($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

// Redirect to login if not authenticated
if (empty($_SESSION['logged_in'])) {
    // Store requested URL for redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Database connection - using absolute path
require_once __DIR__ . '/dbconnect.php';

// Verify shop admin still exists
if (!empty($_SESSION['shop_admin_id'])) {
    try {
        $stmt = $conn->prepare("SELECT id FROM shop_admin WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("i", $_SESSION['shop_admin_id']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 0) {
            // Comprehensive session destruction
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(), 
                    '', 
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
            header("Location: login.php?reason=invalid_account");
            exit();
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Authentication check failed: " . $e->getMessage());
    }
}

// Additional security checks to prevent session hijacking
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_regenerate_id(true);
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

if (!isset($_SESSION['ip_address'])) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
} elseif ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
}
?>
