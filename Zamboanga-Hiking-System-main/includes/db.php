<?php
/**
 * Database Connection File
 */

// Include configuration
require_once __DIR__ . '/config.php';

// Database credentials
$host = 'localhost';
$dbname = 'zamboanga_hiking';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Log error (In production, don't display sensitive information)
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}