<?php
session_start();
include '../includes/db.php'; // adjust path to your DB connection

$trails = []; // define the variable so it's never null

try {
    $stmt = $pdo->query("SELECT * FROM trails"); // fetch from your trails table
    if ($stmt) {
        $trails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // optional: log or display error
    $trails = [];
}

// Sample trail images mapping - Replace with your actual database image URLs
$trailImages = [
    1 => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1920&q=80',
    2 => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=80',
    3 => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1920&q=80',
    4 => 'https://images.unsplash.com/photo-1519904981063-b0cf448d479e?w=1920&q=80',
    5 => 'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=1920&q=80',
    6 => 'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=1920&q=80',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zamboanga Hiking System</title>
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

/* Dynamic Background Container */
.dynamic-background {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 0;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    background-size: cover;
    background-position: center;
}

.dynamic-background::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: inherit;
    filter: blur(50px) brightness(0.6);
    transform: scale(1.1);
    opacity: 0;
    transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.dynamic-background.active {
    opacity: 1;
}

.dynamic-background.active::before {
    opacity: 0.7;
}

/* Trail Overlay */
.trail-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(5, 46, 22, 0.5), rgba(22, 163, 74, 0.3));
    opacity: 0;
    pointer-events: none;
    z-index: 1;
    transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.trail-overlay.active {
    opacity: 1;
}

/* Navbar */
nav {
    background: linear-gradient(135deg, rgba(5, 46, 22, 0.98) 0%, rgba(20, 83, 45, 0.98) 50%, rgba(22, 101, 52, 0.98) 100%);
    padding: 1.3rem 3rem;
    box-shadow: 0 8px 32px rgba(5, 46, 22, 0.4), 0 4px 12px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(24px) saturate(200%);
    border-bottom: 1px solid rgba(74, 222, 128, 0.2);
}

nav::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(74, 222, 128, 0.5), transparent);
}

nav a {
    color: #fff;
    text-decoration: none;
    padding: 0.8rem 1.6rem;
    border-radius: 16px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 700;
    font-size: 0.85rem;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

nav a:first-child {
    margin-right: auto;
}

nav a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s ease;
}

nav a:hover::before {
    left: 100%;
}

nav a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #4ade80, #86efac);
    transform: translateX(-50%);
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 8px rgba(74, 222, 128, 0.6);
}

nav a:hover::after {
    width: 80%;
}

nav a:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 222, 128, 0.3);
}

nav a:active {
    transform: translateY(0);
}

nav span {
    color: #bbf7d0;
    font-weight: 800;
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    padding: 0.6rem 1.4rem;
    background: rgba(74, 222, 128, 0.15);
    border-radius: 50px;
    border: 1px solid rgba(74, 222, 128, 0.3);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

nav span::before {
    content: '👋';
    font-size: 1.1rem;
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

/* Hero Section */
.hero-section {
    background: 
        linear-gradient(135deg, rgba(5, 46, 22, 0.92) 0%, rgba(20, 83, 45, 0.85) 40%, rgba(22, 101, 52, 0.80) 100%),
        url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=80');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    padding: 8rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: 4rem;
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 15% 25%, rgba(34, 197, 94, 0.35), transparent 45%),
        radial-gradient(circle at 85% 75%, rgba(74, 222, 128, 0.3), transparent 45%),
        radial-gradient(circle at 50% 50%, rgba(22, 163, 74, 0.2), transparent 60%);
    animation: heroGlow 12s ease-in-out infinite;
    mix-blend-mode: overlay;
    pointer-events: none;
}

.hero-section::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 180px;
    background: linear-gradient(to top, rgba(240, 253, 244, 1), rgba(240, 253, 244, 0.95) 30%, transparent);
    pointer-events: none;
}

@keyframes heroGlow {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 1000px;
    margin: 0 auto;
    animation: fadeInUp 1s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-badge {
    display: inline-block;
    background: rgba(34, 197, 94, 0.2);
    color: #fff;
    padding: 0.7rem 2rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 2rem;
    backdrop-filter: blur(16px) saturate(180%);
    border: 2px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.hero-badge:hover {
    transform: scale(1.08);
    box-shadow: 0 12px 40px rgba(34, 197, 94, 0.4);
    background: rgba(34, 197, 94, 0.3);
}

.hero-section h1 {
    font-size: 5rem;
    color: #fff;
    margin-bottom: 1.5rem;
    font-weight: 900;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3), 0 0 30px rgba(34, 197, 94, 0.5);
    letter-spacing: -2px;
    line-height: 1.1;
}

.highlight {
    background: linear-gradient(120deg, #4ade80, #86efac, #bbf7d0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 0 20px rgba(74, 222, 128, 0.5));
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { filter: drop-shadow(0 0 20px rgba(74, 222, 128, 0.5)); }
    50% { filter: drop-shadow(0 0 30px rgba(74, 222, 128, 0.8)); }
}

.hero-section p {
    font-size: 1.4rem;
    color: #dcfce7;
    margin-bottom: 3.5rem;
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.3);
    font-weight: 500;
    line-height: 1.8;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

/* Search Container */
.search-container {
    position: relative;
    max-width: 700px;
    margin: 0 auto 4rem;
}

.search-container input {
    width: 100%;
    padding: 1.5rem 4.5rem 1.5rem 2rem;
    border: none;
    border-radius: 60px;
    font-size: 1.05rem;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25), 0 8px 16px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    border: 3px solid transparent;
}

.search-container input:focus {
    outline: none;
    box-shadow: 0 25px 80px rgba(22, 163, 74, 0.3), 0 12px 24px rgba(0, 0, 0, 0.15);
    transform: translateY(-4px);
    border-color: rgba(34, 197, 94, 0.5);
}

.search-container input::placeholder {
    color: #94a3b8;
}

.search-icon {
    position: absolute;
    right: 2rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.5rem;
    opacity: 0.6;
    pointer-events: none;
}

.search-icon::before {
    content: '🔍';
}

/* Stats Container */
.stats-container {
    display: flex;
    justify-content: center;
    gap: 3.5rem;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
    padding: 2rem 2.5rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    backdrop-filter: blur(16px) saturate(180%);
    border: 2px solid rgba(255, 255, 255, 0.3);
    min-width: 180px;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    position: relative;
    overflow: hidden;
}

.stat-item::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3), transparent 70%);
    opacity: 0;
    transition: opacity 0.5s;
}

.stat-item:hover::before {
    opacity: 1;
}

.stat-item:hover {
    transform: translateY(-12px) scale(1.08);
    background: rgba(255, 255, 255, 0.35);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
}

.stat-number {
    display: block;
    font-size: 3.5rem;
    font-weight: 900;
    color: #fff;
    text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.4), 0 0 20px rgba(74, 222, 128, 0.4);
    letter-spacing: -2px;
    position: relative;
}

.stat-label {
    display: block;
    font-size: 1rem;
    color: #dcfce7;
    margin-top: 0.6rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Container */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2.5rem;
    position: relative;
    z-index: 2;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 4rem;
}

.section-header h2 {
    font-size: 3.8rem;
    background: linear-gradient(135deg, #14532d, #15803d, #16a34a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
    font-weight: 900;
    letter-spacing: -2px;
    position: relative;
    display: inline-block;
}

.section-header h2::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 6px;
    background: linear-gradient(90deg, #22c55e, #4ade80, #86efac);
    border-radius: 3px;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
}

.subtitle {
    color: #15803d;
    font-size: 1.3rem;
    font-weight: 500;
    margin-top: 1.2rem;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    justify-content: center;
    gap: 1.2rem;
    margin-bottom: 4rem;
    flex-wrap: wrap;
}

.filter-btn {
    background: #fff;
    border: 2.5px solid #86efac;
    padding: 1rem 2.5rem;
    border-radius: 60px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 1rem;
    font-weight: 700;
    color: #15803d;
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.1);
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.filter-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(34, 197, 94, 0.1);
    transform: translate(-50%, -50%);
    transition: width 0.5s, height 0.5s;
}

.filter-btn:hover::before {
    width: 300px;
    height: 300px;
}

.filter-btn:hover {
    background: #f0fdf4;
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(22, 163, 74, 0.25);
    border-color: #22c55e;
}

.filter-btn:active {
    transform: translateY(-2px);
}

.filter-btn.active {
    background: linear-gradient(135deg, #15803d, #16a34a, #22c55e);
    color: #fff;
    border-color: #14532d;
    box-shadow: 0 8px 32px rgba(22, 163, 74, 0.4);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 7rem 2rem;
    background: linear-gradient(135deg, #fff, #f8fafc);
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    border: 2px solid rgba(22, 163, 74, 0.1);
}

.empty-state-icon {
    font-size: 7rem;
    margin-bottom: 2rem;
    filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.15));
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.empty-state h3 {
    font-size: 2.5rem;
    color: #15803d;
    margin-bottom: 1.2rem;
    font-weight: 900;
}

.empty-state p {
    color: #475569;
    font-size: 1.2rem;
    font-weight: 500;
}

/* Trails Grid */
.trails-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 3rem;
    list-style: none;
}

/* Trail Card */
.trail-card {
    background: #fff;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.08);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.06);
    cursor: pointer;
    transform-style: preserve-3d;
}

.trail-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(22, 163, 74, 0.05), transparent);
    opacity: 0;
    transition: opacity 0.5s;
    pointer-events: none;
    z-index: 1;
}

.trail-card:hover::before {
    opacity: 1;
}

.trail-card:hover {
    transform: translateY(-16px) scale(1.02);
    box-shadow: 0 32px 80px rgba(22, 163, 74, 0.25);
    border-color: rgba(34, 197, 94, 0.3);
    z-index: 10;
}

.trail-image {
    height: 260px;
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.trail-image::before {
    content: '🏔️';
    position: absolute;
    font-size: 12rem;
    bottom: -40px;
    right: -40px;
    opacity: 0.2;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.trail-card:hover .trail-image::before {
    transform: scale(1.15) rotate(8deg);
    opacity: 0.3;
}

.popular-badge {
    position: absolute;
    top: 1.2rem;
    left: 1.2rem;
    background: linear-gradient(135deg, #f97316, #fb923c, #fdba74);
    color: #fff;
    padding: 0.7rem 1.4rem;
    border-radius: 60px;
    font-size: 0.85rem;
    font-weight: 700;
    box-shadow: 0 8px 24px rgba(249, 115, 22, 0.4);
    letter-spacing: 0.5px;
    backdrop-filter: blur(8px);
    z-index: 2;
}

.difficulty-badge {
    position: absolute;
    top: 1.2rem;
    right: 1.2rem;
    padding: 0.7rem 1.4rem;
    border-radius: 60px;
    font-size: 0.85rem;
    font-weight: 700;
    backdrop-filter: blur(12px);
    color: #fff;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    letter-spacing: 0.5px;
    z-index: 2;
}

.difficulty-easy {
    background: linear-gradient(135deg, #22c55e, #4ade80);
}

.difficulty-moderate {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
}

.difficulty-hard {
    background: linear-gradient(135deg, #ef4444, #f87171);
}

/* Trail Content */
.trail-content {
    padding: 2rem;
}

.trail-content h3 {
    font-size: 1.7rem;
    margin-bottom: 1.3rem;
    color: #14532d;
    font-weight: 900;
    letter-spacing: -0.5px;
}

.trail-content h3 a {
    color: #14532d;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.trail-content h3 a::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 0;
    height: 4px;
    background: linear-gradient(90deg, #16a34a, #4ade80);
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 2px;
}

.trail-content h3 a:hover::after {
    width: 100%;
}

.trail-content h3 a:hover {
    color: #16a34a;
}

.trail-meta {
    display: flex;
    gap: 1.8rem;
    margin-bottom: 1.3rem;
    flex-wrap: wrap;
}

.meta-item {
    color: #15803d;
    font-weight: 700;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.meta-item::before {
    content: '📍';
    font-size: 1.2rem;
}

.meta-item:nth-child(2)::before {
    content: '⏱️';
}

.meta-item:nth-child(3)::before {
    content: '⛰️';
}

.trail-content p {
    color: #334155;
    margin-bottom: 1.3rem;
    line-height: 1.8;
    font-weight: 500;
}

/* Trail Features */
.trail-features {
    display: flex;
    gap: 0.7rem;
    flex-wrap: wrap;
    margin-bottom: 1.8rem;
}

.feature-tag {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    color: #15803d;
    padding: 0.6rem 1.2rem;
    border-radius: 24px;
    font-size: 0.85rem;
    font-weight: 700;
    border: 1.5px solid #bbf7d0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.feature-tag:hover {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
}

/* Trail Footer */
.trail-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1.5rem;
    border-top: 2px solid #f1f5f9;
}

.rating {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-weight: 700;
    color: #15803d;
    font-size: 1.1rem;
}

.rating-stars {
    color: #fbbf24;
    font-size: 1.2rem;
    filter: drop-shadow(0 2px 6px rgba(251, 191, 36, 0.4));
}

.view-btn {
    background: linear-gradient(135deg, #15803d, #16a34a, #22c55e);
    color: #fff;
    padding: 0.9rem 2rem;
    border-radius: 60px;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 24px rgba(22, 163, 74, 0.35);
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.view-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.view-btn:hover::before {
    width: 350px;
    height: 350px;
}

.view-btn:hover {
    background: linear-gradient(135deg, #14532d, #15803d, #16a34a);
    transform: translateX(6px);
    box-shadow: 0 12px 40px rgba(22, 163, 74, 0.5);
}

.view-btn:active {
    transform: translateX(3px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        padding: 5rem 1.5rem;
        min-height: 70vh;
    }

    .hero-section h1 {
        font-size: 3rem;
    }
    
    .hero-section p {
        font-size: 1.15rem;
    }
    
    .section-header h2 {
        font-size: 2.5rem;
    }
    
    .trails-grid {
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }
    
    nav {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem 1.5rem;
    }
    
    .stats-container {
        gap: 2rem;
    }
    
    .stat-item {
        min-width: 150px;
        padding: 1.8rem;
    }
    
    .stat-number {
        font-size: 3rem;
    }
    
    .search-container {
        max-width: 100%;
    }
    
    .filter-tabs {
        gap: 1rem;
    }
    
    .filter-btn {
        padding: 0.9rem 1.8rem;
        font-size: 0.95rem;
    }
}

@media (max-width: 480px) {
    .hero-section h1 {
        font-size: 2.2rem;
    }

    .trails-grid {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>
    <!-- DYNAMIC BACKGROUND -->
    <div class="dynamic-background"></div>
    <div class="trail-overlay"></div>

    <!-- NAVBAR -->
    <nav>
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_username']) ?></span>
            <a href="logout_user.php">Logout</a>
        <?php else: ?>
            <a href="login_user.php">User Login</a>
            <a href="register_user.php">Register</a>
        <?php endif; ?>
    </nav>

    <!-- FLOATING LEAVES -->
    <div class="floating-leaves">
        <div class="leaf" style="left: 10%; animation-duration: 15s;">🍃</div>
        <div class="leaf" style="left: 30%; animation-duration: 18s; animation-delay: 3s;">🌿</div>
        <div class="leaf" style="left: 50%; animation-duration: 20s; animation-delay: 6s;">🍃</div>
        <div class="leaf" style="left: 70%; animation-duration: 17s; animation-delay: 2s;">🌿</div>
        <div class="leaf" style="left: 85%; animation-duration: 19s; animation-delay: 5s;">🍃</div>
    </div>

    <!-- HERO SECTION -->
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-badge"> WILDERNESS AWAITS</div>
            <h1>Explore Nature's <span class="highlight">Wonders</span></h1>
            <p>Discover breathtaking trails, embrace adventure, and reconnect with the great outdoors in zamboanga city</p>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search trails by name, location, or difficulty...">
                <span class="search-icon"></span>
            </div>
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-number"><?= count($trails) ?></span>
                    <span class="stat-label">Amazing Trails</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Km Explored</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">1K+</span>
                    <span class="stat-label">Happy Hikers</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TRAILS SECTION -->
    <div class="container">
        <div class="section-header">
            <h2>Featured Trails</h2>
            <p class="subtitle">Handpicked adventures for every explorer</p>
        </div>

        <div class="filter-tabs">
            <button class="filter-btn active" data-filter="all"><span> All Trails</span></button>
            <button class="filter-btn" data-filter="easy"><span> Easy</span></button>
            <button class="filter-btn" data-filter="moderate"><span> Moderate</span></button>
            <button class="filter-btn" data-filter="hard"><span>Hard</span></button>
        </div>

        <?php if (empty($trails)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏔️</div>
                <h3>No Trails Available Yet</h3>
                <p>Check back soon for exciting new trails to explore!</p>
            </div>
        <?php else: ?>
            <ul class="trails-grid" id="trailsGrid">
                <?php 
                $features = [
                    ['🌊 Waterfall', '🌺 Wildflowers', '🦅 Wildlife'],
                    ['🌲 Forest Path', '🏞️ Scenic Views', '📸 Photo Spots'],
                    ['⛺ Camping', '🎣 Fishing', '🚴 Biking'],
                    ['🌅 Sunrise Views', '🌙 Night Hikes', '☁️ Cloud Forest']
                ];
                $index = 0;
                foreach ($trails as $trail): 
                    $isPopular = $index % 3 === 0;
                    $trailId = $trail['id'];
                    // Use image from database or fallback to default images
                    $trailImage = $trail['image_url'] ?? ($trailImages[$trailId] ?? $trailImages[1]);
                ?>
                    <li class="trail-card" 
                        data-difficulty="<?= strtolower($trail['difficulty'] ?? 'moderate') ?>"
                        data-image="<?= htmlspecialchars($trailImage) ?>"
                        data-trail-id="<?= $trailId ?>">
                        <div class="trail-image" style="background-image: url('<?= htmlspecialchars($trailImage) ?>'); background-size: cover; background-position: center;">
                            <?php if ($isPopular): ?>
                                <span class="popular-badge">🔥 Popular</span>
                            <?php endif; ?>
                            <span class="difficulty-badge difficulty-<?= strtolower($trail['difficulty'] ?? 'moderate') ?>">
                                <?= ucfirst($trail['difficulty'] ?? 'Moderate') ?>
                            </span>
                        </div>
                        <div class="trail-content">
                            <h3>
                                <a href="trail.php?id=<?= $trail['id'] ?>"><?= htmlspecialchars($trail['name']) ?></a>
                            </h3>
                            <div class="trail-meta">
                                <span class="meta-item"><?= $trail['distance'] ?? '5.2' ?> km</span>
                                <span class="meta-item"><?= $trail['duration'] ?? '2-3' ?> hrs</span>
                                <span class="meta-item"><?= $trail['elevation'] ?? '450' ?> m</span>
                            </div>
                            <p><?= htmlspecialchars($trail['description'] ?? 'A beautiful trail awaiting your exploration.') ?></p>
                            <div class="trail-features">
                                <?php foreach (array_slice($features[$index % 4], 0, 3) as $feature): ?>
                                    <span class="feature-tag"><?= $feature ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="trail-footer">
                                <div class="rating">
                                    <span class="rating-stars">⭐⭐⭐⭐⭐</span>
                                    <?= $trail['rating'] ?? '4.8' ?> <span style="color:#9ca3af">(<?= $trail['reviews'] ?? '234' ?>)</span>
                                </div>
                                <a href="pages/trail.php?id=<?= $trail['id'] ?>" class="view-btn">Explore Trail →</a>
                            </div>
                        </div>
                    </li>
                <?php 
                    $index++;
                endforeach; 
                ?>
            </ul>
        <?php endif; ?>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const trailCards = document.querySelectorAll('.trail-card');
        const filterBtns = document.querySelectorAll('.filter-btn');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                trailCards.forEach(card => {
                    card.style.display = card.textContent.toLowerCase().includes(term) ? 'block' : 'none';
                });
            });
        }

        // Filter functionality
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.dataset.filter;

                trailCards.forEach(card => {
                    if (filter === 'all' || card.dataset.difficulty === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Dynamic Background Effect
        document.addEventListener('DOMContentLoaded', function() {
            const dynamicBg = document.querySelector('.dynamic-background');
            const overlay = document.querySelector('.trail-overlay');
            const cards = document.querySelectorAll('.trail-card');

            cards.forEach((card) => {
                // Mouse enter - show background
                card.addEventListener('mouseenter', function() {
                    const bgImage = this.getAttribute('data-image');
                    dynamicBg.style.backgroundImage = `url('${bgImage}')`;
                    dynamicBg.classList.add('active');
                    overlay.classList.add('active');
                });

                // Mouse leave - hide background
                card.addEventListener('mouseleave', function() {
                    // Check if card is locked
                    if (!this.classList.contains('bg-locked')) {
                        dynamicBg.classList.remove('active');
                        overlay.classList.remove('active');
                    }
                });

                // Optional: Click to lock background
                card.addEventListener('click', function(e) {
                    // Only if not clicking a link
                    if (!e.target.closest('a') && !e.target.closest('.view-btn')) {
                        const isLocked = this.classList.contains('bg-locked');
                        
                        // Remove lock from all cards
                        cards.forEach(c => c.classList.remove('bg-locked'));
                        
                        if (!isLocked) {
                            this.classList.add('bg-locked');
                        } else {
                            dynamicBg.classList.remove('active');
                            overlay.classList.remove('active');
                        }
                    }
                });
            });

            // Clear background when clicking outside cards
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.trail-card')) {
                    cards.forEach(card => card.classList.remove('bg-locked'));
                    dynamicBg.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });
    </script>

<?php require_once "../includes/footer.php"; ?>
</body>
</html>