<?php
/**
 * Authentication Check File
 * Include this at the top of protected pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if admin is logged in
 * Redirects to login page if not authenticated
 */
function check_login() {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        // Redirect to login page
        header('Location: login.php');
        exit();
    }

    // Optional: Check if user session is valid (not expired)
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = 3600; // 1 hour in seconds
        
        if (time() - $_SESSION['last_activity'] > $inactive_time) {
            // Session expired
            session_unset();
            session_destroy();
            header('Location: login.php?timeout=1');
            exit();
        }
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();

    // Optional: Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Check if user (non-admin) is logged in
 * Redirects to login page if not authenticated
 */
function check_user_login() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page
        header('Location: login_user.php');
        exit();
    }

    // Update last activity time
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    } else {
        $inactive_time = 3600; // 1 hour
        if (time() - $_SESSION['last_activity'] > $inactive_time) {
            session_unset();
            session_destroy();
            header('Location: login_user.php?timeout=1');
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Get current logged-in admin username
 */
function get_admin_username() {
    return isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
}

/**
 * Get current logged-in user username
 */
function get_user_username() {
    return isset($_SESSION['user_username']) ? $_SESSION['user_username'] : 'User';
}

/**
 * Check if admin is logged in (returns boolean)
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

/**
 * Check if user is logged in (returns boolean)
 */
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Logout admin
 */
function admin_logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

/**
 * Logout user
 */
function user_logout() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}