<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Include config and db
require_once '../../includes/config.php';
include '../../includes/db.php';

// Define BASE_URL if not already defined in config
if (!defined('BASE_URL')) {
    define('BASE_URL', SITE_URL . '/public/');
}

// Check if ID parameter exists
if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

// Get and validate trail ID
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if($id === false) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

// Fetch trail from database
$stmt = $pdo->prepare("SELECT * FROM trails WHERE id = ?");
$stmt->execute([$id]);
$trail = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if trail exists
if(!$trail){
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

// ============================================
// TRACK TRAIL VIEW - Start
// ============================================
try {
    // Get user ID if logged in, otherwise null
    $viewer_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Get visitor's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Check if this view should be counted (prevent duplicate counts within 1 hour)
    $should_count = true;
    
    // Create a unique session key for this trail
    $session_key = 'trail_view_' . $id;
    
    // Check if user/visitor already viewed this trail in the last hour
    if (isset($_SESSION[$session_key])) {
        $last_view_time = $_SESSION[$session_key];
        $time_diff = time() - $last_view_time;
        
        // Only count if more than 1 hour (3600 seconds) has passed
        if ($time_diff < 3600) {
            $should_count = false;
        }
    }
    
    // Insert view record if should count
    if ($should_count) {
        $stmt = $pdo->prepare("
            INSERT INTO trail_views (trail_id, user_id, ip_address, viewed_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$id, $viewer_user_id, $ip_address]);
        
        // Update session to mark this view
        $_SESSION[$session_key] = time();
    }
} catch (PDOException $e) {
    // Silently fail - don't break the page if tracking fails
    error_log("Trail view tracking error: " . $e->getMessage());
}
// ============================================
// TRACK TRAIL VIEW - End
// ============================================

// Set default values for optional fields
$trail['distance'] = $trail['distance'] ?? '5.2';
$trail['duration'] = $trail['duration'] ?? '2-3';
$trail['elevation'] = $trail['elevation'] ?? '450';
$trail['difficulty'] = $trail['difficulty'] ?? 'moderate';
$trail['location'] = $trail['location'] ?? 'Nature Reserve';
$trail['rating'] = $trail['rating'] ?? '4.8';
$trail['reviews'] = $trail['reviews'] ?? '287';

// Get trail image - check database first, then fallback
$trailImage = '';
if (!empty($trail['image'])) {
    $imagePath = "../../public/assets/uploads/" . $trail['image'];
    if (file_exists($imagePath)) {
        $trailImage = BASE_URL . "assets/uploads/" . htmlspecialchars($trail['image']);
    }
}

// Fallback to default Unsplash image if no custom image
if (empty($trailImage)) {
    $trailImage = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=80';
}

// Function to get difficulty class
function getDifficultyClass($difficulty) {
    $diff = strtolower($difficulty);
    if($diff === 'easy') return 'easy';
    if($diff === 'hard' || $diff === 'difficult') return 'hard';
    return 'moderate';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($trail['name']) ?> - Trail Details</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<style>
/* Reset & Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
    color: #0f172a;
    line-height: 1.6;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 20% 30%, rgba(34, 197, 94, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(74, 222, 128, 0.08) 0%, transparent 50%),
        url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><circle cx="30" cy="30" r="1" fill="%2316a34a" opacity="0.1"/></svg>');
    pointer-events: none;
    z-index: 0;
}

/* Navbar */
nav {
    background: linear-gradient(135deg, rgba(5, 46, 22, 0.95) 0%, rgba(20, 83, 45, 0.95) 50%, rgba(22, 101, 52, 0.95) 100%);
    padding: 1.2rem 2.5rem;
    box-shadow: 0 4px 30px rgba(5, 46, 22, 0.3), 0 2px 8px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 2rem;
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(20px) saturate(180%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

nav a {
    color: #fff;
    text-decoration: none;
    padding: 0.7rem 1.4rem;
    border-radius: 14px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    font-size: 0.95rem;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.3px;
}

nav a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
    transition: left 0.6s ease;
}

nav a:hover::before {
    left: 100%;
}

nav a:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

nav a:active {
    transform: translateY(0);
}

nav span {
    color: #bbf7d0;
    font-weight: 700;
    margin-left: auto;
    font-size: 0.95rem;
    letter-spacing: 0.5px;
}

/* Floating Leaves Animation */
.floating-leaves {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}

.leaf {
    position: absolute;
    top: -50px;
    font-size: 2rem;
    animation: float-down linear infinite;
    opacity: 0.5;
    filter: drop-shadow(0 2px 6px rgba(22, 163, 74, 0.3));
}

@keyframes float-down {
    0% {
        transform: translateY(-100px) rotate(0deg) translateX(0);
        opacity: 0;
    }
    10% {
        opacity: 0.5;
    }
    50% {
        transform: translateY(50vh) rotate(180deg) translateX(100px);
    }
    90% {
        opacity: 0.5;
    }
    100% {
        transform: translateY(100vh) rotate(360deg) translateX(-50px);
        opacity: 0;
    }
}

/* Breadcrumb */
.breadcrumb {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 2.5rem 1rem;
    position: relative;
    z-index: 2;
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #15803d;
    font-size: 0.95rem;
    flex-wrap: wrap;
    background: rgba(255, 255, 255, 0.8);
    padding: 12px 24px;
    border-radius: 16px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 16px rgba(5, 150, 105, 0.08);
    border: 1px solid rgba(22, 163, 74, 0.15);  
}

.breadcrumb-nav a {
    color: #16a34a;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 8px;
}

.breadcrumb-nav a:hover {
    color: #15803d;
    background: #f0fdf4;
}

.breadcrumb-nav span {
    opacity: 0.4;
    font-weight: 600;
}

/* Trail Hero Section */
.trail-hero {
    position: relative;
    background:
        linear-gradient(135deg, rgba(5, 46, 22, 0.92) 0%, rgba(20, 83, 45, 0.85) 40%, rgba(22, 101, 52, 0.80) 100%),
        url('<?= $trailImage ?>');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    padding: 6rem 2rem;
    margin: 0 auto 4rem;
    max-width: 1400px;
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.trail-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 15% 25%, rgba(34, 197, 94, 0.35), transparent 45%),
        radial-gradient(circle at 85% 75%, rgba(74, 222, 128, 0.3), transparent 45%);
    animation: heroGlow 12s ease-in-out infinite;
    mix-blend-mode: overlay;
}

@keyframes heroGlow {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

.trail-hero-content {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
    color: white;
}

.trail-hero-badges {
    display: flex;
    gap: 14px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.hero-badge {
    padding: 12px 28px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 900;
    letter-spacing: 1px;
    backdrop-filter: blur(12px);
    border: 3px solid;
    text-transform: uppercase;
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-badge:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.35);
}

.badge-difficulty {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.95), rgba(245, 158, 11, 0.95));
    border-color: rgba(245, 158, 11, 0.6);
}

.badge-difficulty.easy {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.95), rgba(22, 163, 74, 0.95));
    border-color: rgba(22, 163, 74, 0.6);
}

.badge-difficulty.hard {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95));
    border-color: rgba(220, 38, 38, 0.6);
}

.badge-featured {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.95), rgba(234, 88, 12, 0.95));
    border-color: rgba(234, 88, 12, 0.6);
}

.trail-hero-content h1 {
    font-size: 4.5rem;
    font-weight: 900;
    margin-bottom: 18px;
    text-shadow: 2px 4px 25px rgba(0,0,0,0.4);
    line-height: 1.1;
    letter-spacing: -2px;
}

.trail-location {
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 35px;
    font-weight: 600;
    opacity: 0.95;
}

.trail-quick-stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.quick-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.1rem;
    font-weight: 800;
    background: rgba(255, 255, 255, 0.15);
    padding: 14px 24px;
    border-radius: 16px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.quick-stat:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
    background: rgba(255, 255, 255, 0.25);
}

.quick-stat-icon {
    font-size: 1.8rem;
}

/* Container */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2.5rem 5rem;
    position: relative;
    z-index: 2;
}

.trail-main-grid {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 40px;
}

/* Trail Content Card */
.trail-content {
    background: #fff;
    border-radius: 28px;
    padding: 50px;
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.06);
}

.section-title {
    font-size: 2.4rem;
    color: #14532d;
    margin-bottom: 32px;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 16px;
    letter-spacing: -1px;
}

.section-title::before {
    content: '';
    width: 8px;
    height: 42px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
}

.trail-description {
    font-size: 1.15rem;
    line-height: 2;
    color: #334155;
    margin-bottom: 45px;
    font-weight: 500;
}

/* Feature Cards */
.trail-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 50px;
}

.feature-card {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    padding: 28px;
    border-radius: 24px;
    text-align: center;
    border: 2px solid #bbf7d0;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.1);
}

.feature-card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 20px 45px rgba(22, 163, 74, 0.25);
    border-color: #22c55e;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
}

.feature-icon {
    font-size: 3.2rem;
    margin-bottom: 14px;
    display: block;
    filter: drop-shadow(0 4px 8px rgba(22, 163, 74, 0.3));
    animation: iconFloat 3s ease-in-out infinite;
}

@keyframes iconFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.feature-label {
    font-weight: 800;
    color: #15803d;
    font-size: 1rem;
    letter-spacing: 0.3px;
}

/* Highlights Section */
.trail-highlights {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    padding: 40px;
    border-radius: 24px;
    border-left: 8px solid #16a34a;
    margin-bottom: 50px;
    box-shadow: 0 8px 30px rgba(22, 163, 74, 0.15);
}

.highlights-title {
    font-size: 1.7rem;
    color: #14532d;
    font-weight: 900;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 14px;
    letter-spacing: -0.5px;
}

.highlights-list {
    list-style: none;
    display: grid;
    gap: 20px;
}

.highlights-list li {
    display: flex;
    align-items: start;
    gap: 16px;
    color: #0f172a;
    font-size: 1.08rem;
    line-height: 1.8;
    font-weight: 600;
    padding: 12px;
    background: rgba(255, 255, 255, 0.6);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.highlights-list li:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateX(8px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
}

.highlights-list li::before {
    content: '✓';
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-weight: 900;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
}

/* Gallery */
.trail-gallery {
    margin-bottom: 45px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.gallery-item {
    aspect-ratio: 4/3;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border: 4px solid white;
}

.gallery-item:hover {
    transform: scale(1.08) translateY(-8px) rotate(2deg);
    box-shadow: 0 25px 60px rgba(22, 163, 74, 0.35);
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s;
}

.gallery-item:hover img {
    transform: scale(1.15);
}

/* Sidebar */
.trail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.sidebar-card {
    background: #fff;
    border-radius: 28px;
    padding: 38px;
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.06);
    transition: all 0.4s ease;
}

.sidebar-card:hover {
    box-shadow: 0 20px 60px rgba(22, 163, 74, 0.15);
    transform: translateY(-5px);
}

.sidebar-title {
    font-size: 1.6rem;
    color: #14532d;
    margin-bottom: 28px;
    font-weight: 900;
    letter-spacing: -0.5px;
}

/* Stats List */
.stats-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 16px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid #bbf7d0;
}

.stat-item:hover {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    transform: translateX(8px);
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.2);
    border-color: #22c55e;
}

.stat-label {
    font-weight: 700;
    color: #15803d;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.05rem;
}

.stat-label-icon {
    font-size: 1.5rem;
}

.stat-value {
    font-weight: 900;
    color: #16a34a;
    font-size: 1.3rem;
    letter-spacing: -0.5px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 20px 36px;
    border-radius: 50px;
    font-weight: 900;
    font-size: 1.1rem;
    text-decoration: none;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    cursor: pointer;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.btn-primary {
    background: linear-gradient(135deg, #15803d, #16a34a, #22c55e);
    color: white;
    box-shadow: 0 8px 30px rgba(22, 163, 74, 0.4);
    border: 3px solid #14532d;
}

.btn-primary:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 15px 45px rgba(22, 163, 74, 0.5);
    background: linear-gradient(135deg, #14532d, #15803d, #16a34a);
}

.btn-secondary {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    color: #15803d;
    border: 3px solid #22c55e;
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.15);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 12px 35px rgba(22, 163, 74, 0.25);
}

/* Rating Card */
.rating-card {
    text-align: center;
}

.rating-number {
    font-size: 4.5rem;
    font-weight: 900;
    color: #16a34a;
    margin-bottom: 14px;
    display: block;
    letter-spacing: -2px;
    text-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
}

.rating-stars {
    font-size: 2.2rem;
    margin-bottom: 14px;
    display: block;
    filter: drop-shadow(0 4px 8px rgba(251, 191, 36, 0.4));
}

.rating-count {
    color: #15803d;
    font-size: 1.1rem;
    font-weight: 700;
}

/* Weather Widget */
.weather-widget {
    background: linear-gradient(135deg, #15803d, #16a34a, #22c55e);
    color: white;
    padding: 35px;
    border-radius: 24px;
    text-align: center;
    box-shadow: 0 12px 40px rgba(22, 163, 74, 0.4);
    position: relative;
    overflow: hidden;
}

.weather-widget::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    animation: weatherPulse 5s ease-in-out infinite;
}

@keyframes weatherPulse {
    0%, 100% { transform: scale(1); opacity: 0.6; }
    50% { transform: scale(1.3); opacity: 0.3; }
}

.weather-icon {
    font-size: 4rem;
    margin-bottom: 14px;
    filter: drop-shadow(0 6px 12px rgba(0,0,0,0.3));
    position: relative;
    z-index: 1;
    animation: weatherFloat 4s ease-in-out infinite;
}

@keyframes weatherFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.weather-temp {
    font-size: 3.2rem;
    font-weight: 900;
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
    letter-spacing: -2px;
}

.weather-desc {
    opacity: 0.95;
    font-size: 1.15rem;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

/* Tips List */
.tips-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.tips-list li {
    display: flex;
    align-items: start;
    gap: 14px;
    color: #0f172a;
    font-size: 1.02rem;
    line-height: 1.8;
    font-weight: 600;
    padding: 16px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 14px;
    transition: all 0.4s ease;
    border: 2px solid #bbf7d0;
}

.tips-list li:hover {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    transform: translateX(6px);
    box-shadow: 0 6px 18px rgba(22, 163, 74, 0.2);
    border-color: #22c55e;
}

.tips-list li::before {
    content: '💡';
    font-size: 1.5rem;
    flex-shrink: 0;
}

/* Map Modal Styles */
.map-modal {
    display: none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.6);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.map-modal-content {
    position: relative;
    width: 90%;
    max-width: 800px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
}

.map-modal-close {
    position: absolute;
    top: 10px; right: 15px;
    font-size: 28px;
    cursor: pointer;
    font-weight: bold;
    color: #333;
    z-index: 10000;
}

/* Responsive Design */
@media (max-width: 968px) {
    .trail-main-grid {
        grid-template-columns: 1fr;
    }
   
    .trail-hero {
        padding: 4rem 2rem;
    }
   
    .trail-hero-content h1 {
        font-size: 3.2rem;
    }
}

@media (max-width: 768px) {
    nav {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem 1.5rem;
    }
   
    nav span {
        margin-left: 0;
    }
   
    .trail-content {
        padding: 32px;
    }
   
    .section-title {
        font-size: 1.9rem;
    }
   
    .trail-features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .trail-hero-content h1 {
        font-size: 2.5rem;
    }
   
    .trail-features-grid {
        grid-template-columns: 1fr;
    }
   
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}

.gallery-grid {
        grid-template-columns: 1fr;
    }
}

/* Live Location Tracking Styles */
.tracking-controls {
    position: absolute;
    bottom: 80px;
    left: 10px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tracking-btn {
    background: white;
    border: 2px solid #16a34a;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.9rem;
    color: #15803d;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.tracking-btn:hover {
    background: #f0fdf4;
}

.tracking-btn.active {
    background: #16a34a;
    color: white;
}

.tracking-status {
    position: absolute;
    top: 60px;
    left: 10px;
    background: white;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
}

.tracking-status.active {
    display: block;
    background: #dcfce7;
    color: #15803d;
    border: 2px solid #22c55e;
}

</style>

</style>
<body>
    <!-- Navbar -->
    <nav>
        <a href="../index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_username']) ?></span>
            <a href="../logout_user.php">Logout</a>
        <?php else: ?>
            <a href="../login_user.php">User Login</a>
            <a href="../register_user.php">Register</a>
        <?php endif; ?>
    </nav>

    <!-- Floating Leaves -->
    <div class="floating-leaves">
        <div class="leaf" style="left: 10%; animation-duration: 15s;">🍃</div>
        <div class="leaf" style="left: 30%; animation-duration: 18s; animation-delay: 3s;">🌿</div>
        <div class="leaf" style="left: 50%; animation-duration: 20s; animation-delay: 6s;">🍃</div>
        <div class="leaf" style="left: 70%; animation-duration: 17s; animation-delay: 2s;">🌿</div>
        <div class="leaf" style="left: 85%; animation-duration: 19s; animation-delay: 5s;">🍃</div>
    </div>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <nav class="breadcrumb-nav">
        <a href="../index.php">🏠 Home</a>
        <span>›</span>
        <a href="../index.php">Trails</a>
            <span>›</span>
            <span><?= htmlspecialchars($trail['name']) ?></span>
        </nav>
    </div>
   
    <!-- Trail Hero -->
    <div class="container">
        <div class="trail-hero">
            <div class="trail-hero-content">
                <div class="trail-hero-badges">
                    <span class="hero-badge badge-difficulty <?= getDifficultyClass($trail['difficulty']) ?>">
                        <?= strtoupper(htmlspecialchars($trail['difficulty'])) ?> DIFFICULTY
                    </span>
                    <span class="hero-badge badge-featured">⭐ FEATURED TRAIL</span>
                </div>
                <h1><?= htmlspecialchars($trail['name']) ?></h1>
                <p class="trail-location">
                    <span>📍</span>
                    <?= htmlspecialchars($trail['location']) ?>
                </p>
                <div class="trail-quick-stats">
                    <div class="quick-stat">
                        <span class="quick-stat-icon">📏</span>
                        <span><?= htmlspecialchars($trail['distance']) ?> km</span>
                    </div>
                    <div class="quick-stat">
                        <span class="quick-stat-icon">⏱️</span>
                        <span><?= htmlspecialchars($trail['duration']) ?> hours</span>
                    </div>
                    <div class="quick-stat">
                        <span class="quick-stat-icon">⛰️</span>
                        <span><?= htmlspecialchars($trail['elevation']) ?>m gain</span>
                    </div>
                    <div class="quick-stat">
                        <span class="quick-stat-icon">🥾</span>
                        <span>Hiking Trail</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="trail-main-grid">
            <!-- Left Column - Main Content -->
            <div>
                <div class="trail-content">
                    <h2 class="section-title">Trail Overview</h2>
                    <p class="trail-description">
                        <?php
                        $description = !empty($trail['description'])
                            ? $trail['description']
                            : 'Embark on an unforgettable journey through pristine wilderness. This trail offers breathtaking views, diverse ecosystems, and an immersive nature experience that will leave you refreshed and inspired. Perfect for adventurers seeking to connect with the great outdoors while enjoying stunning panoramic vistas and encountering local wildlife in their natural habitat.';
                        echo nl2br(htmlspecialchars($description));
                        ?>
                    </p>

                    <div class="trail-features-grid">
                        <div class="feature-card">
                            <span class="feature-icon">🌲</span>
                            <div class="feature-label">Forest Path</div>
                        </div>
                        <div class="feature-card">
                            <span class="feature-icon">🌊</span>
                            <div class="feature-label">Water Features</div>
                        </div>
                        <div class="feature-card">
                            <span class="feature-icon">🦅</span>
                            <div class="feature-label">Wildlife Viewing</div>
                        </div>
                        <div class="feature-card">
                            <span class="feature-icon">📸</span>
                            <div class="feature-label">Photo Opportunities</div>
                        </div>
                    </div>

                    <div class="trail-highlights">
                        <h3 class="highlights-title">🌟 Trail Highlights</h3>
                        <ul class="highlights-list">
                            <li>Spectacular panoramic views from multiple scenic viewpoints</li>
                            <li>Well-maintained trails with clear signage throughout</li>
                            <li>Diverse flora and fauna with excellent wildlife spotting opportunities</li>
                            <li>Natural water sources and refreshing stream crossings</li>
                            <li>Perfect for photography enthusiasts and nature lovers</li>
                        </ul>
                    </div>

                    <?php if(!empty($trail['image'])): ?>
                    <div class="trail-gallery">
                        <h2 class="section-title">Photo Gallery</h2>
                        <div class="gallery-grid">
                            <div class="gallery-item">
                                <img src="<?= $trailImage ?>"
                                     alt="<?= htmlspecialchars($trail['name']) ?> view"
                                     onerror="this.parentElement.style.display='none'">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="trail-sidebar">
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Trail Stats</h3>
                    <div class="stats-list">
                        <div class="stat-item">
                            <span class="stat-label">
                                <span class="stat-label-icon">📏</span>
                                Distance
                            </span>
                            <span class="stat-value"><?= htmlspecialchars($trail['distance']) ?> km</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <span class="stat-label-icon">⏱️</span>
                                Duration
                            </span>
                            <span class="stat-value"><?= htmlspecialchars($trail['duration']) ?> hrs</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <span class="stat-label-icon">⛰️</span>
                                Elevation Gain
                            </span>
                            <span class="stat-value"><?= htmlspecialchars($trail['elevation']) ?>m</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <span class="stat-label-icon">🎯</span>
                                Difficulty
                            </span>
                            <span class="stat-value"><?= ucfirst(htmlspecialchars($trail['difficulty'])) ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <span class="stat-label-icon">🚶</span>
                                Route Type
                            </span>
                            <span class="stat-value">Loop</span>
                        </div>
                    </div>
                </div>

                <div class="sidebar-card rating-card">
                    <h3 class="sidebar-title">Trail Rating</h3>
                    <span class="rating-number"><?= htmlspecialchars($trail['rating']) ?></span>
                    <span class="rating-stars">⭐⭐⭐⭐⭐</span>
                    <p class="rating-count"><?= htmlspecialchars($trail['reviews']) ?> reviews from hikers</p>
                </div>

                <div class="sidebar-card">
                    <div class="weather-widget">
                        <div class="weather-icon">⛅</div>
                        <div class="weather-temp">24°C</div>
                        <div class="weather-desc">Perfect hiking weather</div>
                    </div>
                </div>

                <div class="sidebar-card">
                    <h3 class="sidebar-title">Essential Tips</h3>
                    <ul class="tips-list">
                        <li>Bring plenty of water and snacks</li>
                        <li>Wear appropriate hiking boots</li>
                        <li>Start early to avoid crowds</li>
                        <li>Check weather conditions before hiking</li>
                        <li>Pack sunscreen and insect repellent</li>
                    </ul>
                </div>
                               
                <!-- Display Approved Reviews FIRST -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">💬 Hiker Reviews</h3>
                    <div id="reviewsList" style="max-height:600px; overflow-y:auto;">
                        <!-- Reviews will load here -->
                    </div>
                </div>

                <!-- User Review Form Section SECOND -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title">📝 Leave a Review</h3>
                   
                    <form id="reviewForm" style="display:flex; flex-direction:column; gap:16px;">
                        <input type="hidden" id="trail_id" value="<?= $id ?>">
                        <input type="hidden" id="user_id" value="<?= $_SESSION['user_id'] ?>">
                       
                        <!-- Star Rating -->
                        <div>
                            <label style="display:block; font-weight:700; color:#15803d; margin-bottom:8px;">Your Rating:</label>
                            <div style="display:flex; gap:8px;">
                                <span class="star" data-value="1" style="font-size:2rem; cursor:pointer; color:#d1d5db;">⭐</span>
                                <span class="star" data-value="2" style="font-size:2rem; cursor:pointer; color:#d1d5db;">⭐</span>
                                <span class="star" data-value="3" style="font-size:2rem; cursor:pointer; color:#d1d5db;">⭐</span>
                                <span class="star" data-value="4" style="font-size:2rem; cursor:pointer; color:#d1d5db;">⭐</span>
                                <span class="star" data-value="5" style="font-size:2rem; cursor:pointer; color:#d1d5db;">⭐</span>
                            </div>
                            <input type="hidden" id="rating" name="rating" required>
                            <span id="ratingError" style="color:#dc2626; font-size:0.875rem; display:none;">Please select a rating</span>
                        </div>
                       
                        <!-- Comment -->
                        <div>
                            <label style="display:block; font-weight:700; color:#15803d; margin-bottom:8px;">Your Comment:</label>
                            <textarea
                                id="comment"
                                name="comment"
                                rows="4"
                                style="width:100%; padding:12px; border:2px solid #bbf7d0; border-radius:12px; font-family:inherit; resize:vertical;"
                                placeholder="Share your hiking experience..."
                                required
                            ></textarea>
                        </div>
                       
                        <!-- Submit Button -->
                        <button type="submit"
                            style="background:linear-gradient(135deg, #16a34a, #22c55e); color:white; padding:14px 24px; border:none; border-radius:50px; font-weight:900; cursor:pointer; font-size:1rem; letter-spacing:0.5px; box-shadow:0 6px 20px rgba(22,163,74,0.4); transition:all 0.3s ease;"
                            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 30px rgba(22,163,74,0.5)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(22,163,74,0.4)';"
                        >Submit Review</button>
                    </form>
                   
                    <div id="reviewMessage" style="margin-top:16px; display:none;"></div>
                </div>
                <?php else: ?>
                <div class="sidebar-card" style="text-align:center; padding:30px;">
                    <p style="color:#15803d; font-weight:600; margin-bottom:16px;">Login to leave a review</p>
                    <a href="<?= BASE_URL ?>login_user.php" class="btn btn-primary" style="display:inline-block;">
                        Login Now
                    </a>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="action-buttons" style="margin:20px 0;">
                    <a href="#" class="btn btn-primary" onclick="openMapModal(event)">
                        <span>🗺️ Get Directions</span>
                    </a>
                    <a href="../index.php" class="btn btn-secondary">
                        <span>← Back to Trails</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div id="mapModal" class="map-modal">
        <div class="map-modal-content" style="max-width: 1200px; height: 90vh;">
            <span class="map-modal-close" onclick="closeMapModal()">×</span>
            <div style="background:#15803d; color:white; padding:15px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:1.2rem;">📍 Directions to <?= htmlspecialchars($trail['name']) ?></h3>
                <button onclick="toggleFullscreen()" 
                    style="background:rgba(255,255,255,0.2); border:none; color:white; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700;">
                    ⛶ Fullscreen
                </button>
            </div>
            <!-- Google Map with Directions -->
            <div id="map" style="width:100%; height:calc(100% - 120px);"></div>
            <div id="trackingStatus" class="tracking-status"></div>
            <div class="tracking-controls">
                <button id="trackingBtn" class="tracking-btn" onclick="toggleTracking()">📍 Start Live Tracking</button>
                <button class="tracking-btn" onclick="centerOnUser()">🎯 Center on Me</button>
            </div>
            <div style="background:#f0fdf4; padding:15px; text-align:center;">
                <p style="color:#15803d; font-weight:600; margin:0;">
                    📌 Blue route shows the hiking direction from your location
                </p>
            </div>
        </div>
    </div>

    </div> <!-- End of container -->

    <!-- Map Modal - ADD THIS ENTIRE SECTION -->
    <div id="mapModal" class="map-modal">
        <div class="map-modal-content" style="max-width: 1200px; height: 90vh;">
            <span class="map-modal-close" onclick="closeMapModal()">×</span>
            <div style="background:#15803d; color:white; padding:15px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:1.2rem;">📍 Directions to <?= htmlspecialchars($trail['name']) ?></h3>
                <button onclick="toggleFullscreen()" 
                    style="background:rgba(255,255,255,0.2); border:none; color:white; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700;">
                    ⛶ Fullscreen
                </button>
            </div>
            <div id="map" style="width:100%; height:calc(100% - 120px);"></div>
            <div style="background:#f0fdf4; padding:15px; text-align:center;">
                <p style="color:#15803d; font-weight:600; margin:0;">
                    📌 Blue route shows the hiking direction from your location
                </p>
            </div>
        </div>
    </div>

<!-- Google Maps API Script -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBmesPIzzfeUhiFP3WGfL5myMimx0wdVbo&libraries=places"></script>

<!-- Scripts -->
<script>
let map;
let directionsService;
let directionsRenderer;

// Star Rating System
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating');
const ratingError = document.getElementById('ratingError');

if(stars.length > 0) {
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingInput.value = value;
            ratingError.style.display = 'none';
           
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.style.color = '#fbbf24';
                } else {
                    s.style.color = '#d1d5db';
                }
            });
        });
    });
}

// Submit Review Form
const reviewForm = document.getElementById('reviewForm');
if(reviewForm) {
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
       
        const rating = document.getElementById('rating').value;
        const comment = document.getElementById('comment').value;
        const trailId = document.getElementById('trail_id').value;
       
        if (!rating) {
            ratingError.style.display = 'block';
            return;
        }
       
        fetch('submit_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                trail_id: trailId,
                rating: rating,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('reviewMessage');
            messageDiv.style.display = 'block';
           
            if (data.success) {
                messageDiv.innerHTML = '<div style="background:#dcfce7; border:2px solid #22c55e; color:#15803d; padding:14px; border-radius:12px; font-weight:600;">✅ ' + data.message + '</div>';
                reviewForm.reset();
                stars.forEach(s => s.style.color = '#d1d5db');
                
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 5000);
            } else {
                messageDiv.innerHTML = '<div style="background:#fee2e2; border:2px solid #ef4444; color:#dc2626; padding:14px; border-radius:12px; font-weight:600;">❌ ' + (data.error || 'Failed to submit review') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const messageDiv = document.getElementById('reviewMessage');
            messageDiv.style.display = 'block';
            messageDiv.innerHTML = '<div style="background:#fee2e2; border:2px solid #ef4444; color:#dc2626; padding:14px; border-radius:12px; font-weight:600;">❌ Error submitting review</div>';
        });
    });
}

// Load Approved Reviews
function loadReviews() {
    const trailId = '<?= $id ?>';
   
    fetch('../get_reviews.php?trail_id=' + trailId)
        .then(response => response.json())
        .then(data => {
            const reviewsList = document.getElementById('reviewsList');
           
            if (data.success && data.reviews && data.reviews.length > 0) {
                reviewsList.innerHTML = data.reviews.map(review => `
                    <div style="border-bottom:2px solid #dcfce7; padding:18px 0;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <div>
                                <span style="font-weight:700; color:#14532d;">${review.username}</span>
                                <span style="font-size:0.85rem; color:#64748b; margin-left:8px;">${new Date(review.created_at).toLocaleDateString()}</span>
                            </div>
                            <div style="color:#fbbf24; font-size:1.1rem;">
                                ${'⭐'.repeat(review.rating)}
                            </div>
                        </div>
                        <p style="color:#334155; line-height:1.6; font-weight:500;">${review.comment}</p>
                    </div>
                `).join('');
            } else {
                reviewsList.innerHTML = '<p style="color:#64748b; text-align:center; padding:20px; font-weight:500;">No reviews yet. Be the first to review this trail!</p>';
            }
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
            document.getElementById('reviewsList').innerHTML = '<p style="color:#dc2626; text-align:center; padding:20px;">Error loading reviews</p>';
        });
}

// Load reviews on page load
loadReviews();

// Map Modal Functions
function openMapModal(e){
    e.preventDefault();
    console.log('openMapModal called'); // Debug
    
    // Get trail coordinates from PHP
    const trailLat = <?= json_encode($trail['latitude']) ?>;
    const trailLng = <?= json_encode($trail['longitude']) ?>;
    const trailName = <?= json_encode($trail['name']) ?>;
    
    console.log('Coordinates:', trailLat, trailLng); // Debug
    
    // Show modal
    const modal = document.getElementById('mapModal');
    console.log('Modal element:', modal); // Debug
    
    if(modal) {
        modal.style.display = 'flex';
        
        // Initialize map after modal is visible
        setTimeout(() => {
            if(trailLat && trailLng) {
                initializeMap(parseFloat(trailLat), parseFloat(trailLng), trailName);
            } else {
                alert('Trail coordinates not available. Please contact admin.');
                closeMapModal();
            }
        }, 100);
    } else {
        console.error('Modal not found!');
    }
}

function initializeMap(destLat, destLng, trailName) {
    // Initialize map centered on trail
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: destLat, lng: destLng },
        zoom: 13,
        mapTypeId: 'terrain'
    });
    
    // Initialize directions service and renderer
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: false,
        polylineOptions: {
            strokeColor: '#2563eb',
            strokeWeight: 6,
            strokeOpacity: 0.8
        }
    });
    
    // Get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                
                // Calculate and display route
                calculateRoute(userLat, userLng, destLat, destLng);
            },
            function(error) {
                console.error('Geolocation error:', error);
                // If location fails, just show the destination marker
                showDestinationOnly(destLat, destLng, trailName);
            }
        );
    } else {
        // Browser doesn't support geolocation
        showDestinationOnly(destLat, destLng, trailName);
    }
}

function calculateRoute(startLat, startLng, endLat, endLng) {
    const origin = new google.maps.LatLng(startLat, startLng);
    const destination = new google.maps.LatLng(endLat, endLng);
    
    const request = {
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode.WALKING,
        unitSystem: google.maps.UnitSystem.METRIC
    };
    
    directionsService.route(request, function(result, status) {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
            
            const route = result.routes[0].legs[0];
            console.log('Distance:', route.distance.text);
            console.log('Duration:', route.duration.text);
        } else {
            console.error('Directions request failed:', status);
            showDestinationOnly(endLat, endLng, 'Trail Location');
        }
    });
}

function showDestinationOnly(lat, lng, name) {
    const position = { lat: lat, lng: lng };
    
    new google.maps.Marker({
        position: position,
        map: map,
        title: name,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#16a34a',
            fillOpacity: 1,
            strokeColor: '#fff',
            strokeWeight: 3
        }
    });
    
    map.setCenter(position);
    map.setZoom(15);
}

function closeMapModal(){
    document.getElementById('mapModal').style.display = 'none';
    if(map) {
        map = null;
    }
}

function toggleFullscreen() {
    const modal = document.getElementById('mapModal');
    const modalContent = modal.querySelector('.map-modal-content');
    
    if (!document.fullscreenElement) {
        if (modalContent.requestFullscreen) {
            modalContent.requestFullscreen();
        } else if (modalContent.webkitRequestFullscreen) {
            modalContent.webkitRequestFullscreen();
        } else if (modalContent.msRequestFullscreen) {
            modalContent.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

// Close modal when clicking outside content
window.addEventListener('DOMContentLoaded', function() {
    const mapModal = document.getElementById('mapModal');
    if(mapModal) {
        mapModal.addEventListener('click', function(e) {
            if(e.target === this) closeMapModal();
        });
    }
});

// ============================================
// REAL-TIME GPS TRACKING - Start
// ============================================
let userMarker = null;
let userAccuracyCircle = null;
let watchId = null;
let isTracking = false;

function startTracking() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }
    
    if (!map) {
        alert('Please open the map first');
        return;
    }
    
    isTracking = true;
    updateTrackingUI();
    
    watchId = navigator.geolocation.watchPosition(
        function(position) {
            const userPos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            if (userMarker) userMarker.setMap(null);
            if (userAccuracyCircle) userAccuracyCircle.setMap(null);
            
            userMarker = new google.maps.Marker({
                position: userPos,
                map: map,
                title: "You are here",
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 12,
                    fillColor: "#4285F4",
                    fillOpacity: 1,
                    strokeColor: "#ffffff",
                    strokeWeight: 3
                },
                zIndex: 999
            });
            
            userAccuracyCircle = new google.maps.Circle({
                center: userPos,
                radius: position.coords.accuracy,
                map: map,
                fillColor: "#4285F4",
                fillOpacity: 0.15,
                strokeColor: "#4285F4",
                strokeWeight: 1
            });
            
            const statusEl = document.getElementById('trackingStatus');
            if (statusEl) {
                statusEl.textContent = '📍 Tracking active - Accuracy: ' + Math.round(position.coords.accuracy) + 'm';
            }
        },
        function(error) {
            console.error('Tracking error:', error);
            const statusEl = document.getElementById('trackingStatus');
            if (statusEl) {
                statusEl.textContent = '⚠️ Unable to get location';
                statusEl.style.background = '#fee2e2';
                statusEl.style.color = '#dc2626';
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

function stopTracking() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
    if (userMarker) { userMarker.setMap(null); userMarker = null; }
    if (userAccuracyCircle) { userAccuracyCircle.setMap(null); userAccuracyCircle = null; }
    isTracking = false;
    updateTrackingUI();
}

function toggleTracking() {
    if (isTracking) { stopTracking(); } else { startTracking(); }
}

function centerOnUser() {
    if (userMarker && map) {
        map.setCenter(userMarker.getPosition());
        map.setZoom(16);
    } else {
        alert('Start tracking first to see your location');
    }
}

function updateTrackingUI() {
    const btn = document.getElementById('trackingBtn');
    const status = document.getElementById('trackingStatus');
    if (btn) {
        btn.textContent = isTracking ? '⏹️ Stop Tracking' : '📍 Start Live Tracking';
        btn.classList.toggle('active', isTracking);
    }
    if (status) {
        status.classList.toggle('active', isTracking);
        if (isTracking) status.textContent = '📍 Getting location...';
    }
}

const originalCloseMapModal = closeMapModal;
closeMapModal = function() {
    stopTracking();
    originalCloseMapModal();
};
// ============================================
// REAL-TIME GPS TRACKING - End
// ============================================



</script>
</body>
</html>