<?php
// auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'httponly' => true,  // Prevent XSS cookie access
        'samesite' => 'Lax'
    ]);
    session_start();
}

/**
 * Ensures the user is logged in. Redirects to login page if unauthenticated.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Restricts page access to specific roles.
 * @param array $allowedRoles Array of permitted role names (e.g., ['Admin'])
 */
function requireRole($allowedRoles = []) {
    requireLogin();
    if (!in_array($_SESSION['role_name'], $allowedRoles)) {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>403 Forbidden</title><link rel='stylesheet' href='css/style.css'></head><body>";
        echo "<div class='container' style='max-width:500px; margin-top:50px; text-align:center;'>";
        echo "<h2>403 - Access Denied</h2>";
        echo "<p>You do not have permission to access the Administrator Portal.</p>";
        echo "<a href='index.php' class='btn' style='display:inline-block; padding:10px 15px; background:var(--primary-color); color:#fff; text-decoration:none; border-radius:6px;'>Return to Customer Booking</a>";
        echo "</div></body></html>";
        exit;
    }
}
?>