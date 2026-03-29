<?php
// ============================================================
// includes/auth_check.php — Session Guard
// Usage: require_once '../includes/auth_check.php';
//        auth_check();           // any logged-in user
//        auth_check('admin');    // admin only
// ============================================================

function auth_check($required_role = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Not logged in at all — send to login
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . get_base_url() . 'auth/login.php');
        exit;
    }

    // Role check
    if ($required_role !== null && $_SESSION['role'] !== $required_role) {
        // Wrong role — redirect to their own dashboard
        if ($_SESSION['role'] === 'admin') {
            header('Location: ' . get_base_url() . 'admin/dashboard.php');
        } else {
            header('Location: ' . get_base_url() . 'user/dashboard.php');
        }
        exit;
    }
}

// Helper: build absolute URL base (works in any subdirectory)
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    // Strip filename and go up directories to reach /nexus/
    $script   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Walk up until we are at /nexus
    $parts    = explode('/', trim($script, '/'));
    $base     = '';
    foreach ($parts as $part) {
        $base .= '/' . $part;
        if (strtolower($part) === 'nexus') break;
    }
    return $protocol . '://' . $host . $base . '/';
}
