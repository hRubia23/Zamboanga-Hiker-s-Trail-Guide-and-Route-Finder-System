<?php
/**
 * Configuration File
 * Define all site constants and configuration settings here
 */

// Site Information
define('SITE_NAME', 'Zamboanga Hiking System');
define('SITE_DESCRIPTION', 'Explore the best hiking trails in Zamboanga');
define('SITE_URL', 'http://localhost/Zamboanga-Hiking-System-main');
define('BASE_URL', 'http://localhost/Zamboanga-Hiking-System-main/public/');

// Database Configuration (if not already in db.php)
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'zamboanga_hiking');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../public/assets/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Contact Information
define('CONTACT_EMAIL', 'info@zamboangahiking.com');
define('CONTACT_PHONE', '+63 XXX XXX XXXX');

// Social Media Links
define('FACEBOOK_URL', '#');
define('TWITTER_URL', '#');
define('INSTAGRAM_URL', '#');

// Admin Settings
define('ADMIN_EMAIL', 'admin@zamboangahiking.com');

// Session Settings (only set if session not started)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
}

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// Version
define('APP_VERSION', '1.0.0');