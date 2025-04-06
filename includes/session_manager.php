
<?php
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Security settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1); // Enable if using HTTPS
        ini_set('session.use_strict_mode', 1);
        
        session_start();
        
        // Regenerate ID to prevent fixation
        if (empty($_SESSION['initiated'])) {
            session_regenerate_id();
            $_SESSION['initiated'] = true;
        }
    }
}
?>