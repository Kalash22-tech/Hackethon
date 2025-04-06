<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Set default session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Enable if using HTTPS
    ini_set('session.use_strict_mode', 1);
}
?>