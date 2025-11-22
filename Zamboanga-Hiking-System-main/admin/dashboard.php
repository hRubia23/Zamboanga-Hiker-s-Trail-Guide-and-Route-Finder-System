<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Fetch total trails count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM trails");
$total_trails = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Fetch all registered users
$stmt = $pdo->query("SELECT username, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// NEW: Fetch pending reviews count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'");
$pending_reviews_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// NEW: Fetch pending reviews with trail and user information
$stmt = $pdo->query("
    SELECT 
        r.id,
        r.rating,
        r.comment,
        r.created_at,
        u.username,
        t.name as trail_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN trails t ON r.trail_id = t.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
");
$pending_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trail Admin - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
            padding: 0;
        }

        /* Header Navigation */
        .header {
            background: linear-gradient(135deg, #1e3a2c, #2d5a3f);
            color: white;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .logo-icon svg {
            width: 28px;
            height: 28px;
            fill: white;
        }

        .logo-text h1 {
            font-family: 'Merriweather', serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .logo-text p {
            font-size: 12px;
            opacity: 0.8;
            font-weight: 400;
        }

        .header-nav {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-link.logout {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .nav-link.logout:hover {
            background: rgba(244, 67, 54, 0.3);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        /* Welcome Section */
        .welcome-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(76, 175, 80, 0.1), transparent);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-content h2 {
            font-family: 'Merriweather', serif;
            font-size: 32px;
            color: #1e3a2c;
            margin-bottom: 10px;
        }

        .welcome-content p {
            color: #5a7a5f;
            font-size: 16px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(76, 175, 80, 0.2);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .stat-icon svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        /* NEW: Orange icon for pending reviews */
        .stat-icon.pending {
            background: linear-gradient(135deg, #FF9800, #FFB74D);
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }

        .stat-details h3 {
            font-size: 14px;
            color: #7a9d7e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #1e3a2c;
            font-family: 'Merriweather', serif;
        }

        /* Action Cards */
        .actions-section {
            margin-top: 30px;
        }

        .section-title {
            font-family: 'Merriweather', serif;
            font-size: 24px;
            color: #1e3a2c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 30px;
            background: linear-gradient(180deg, #4CAF50, #8BC34A);
            border-radius: 2px;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .action-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 15px;
            position: relative;
            overflow: hidden;
        }

        .action-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 0;
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            transition: height 0.3s ease;
            z-index: 1;
        }

        .action-card:hover::after {
            height: 100%;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(76, 175, 80, 0.25);
        }

        .action-card:hover .action-content,
        .action-card:hover .action-icon {
            position: relative;
            z-index: 2;
            color: white;
        }

        .action-card:hover .action-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .action-card:hover .action-icon svg {
            fill: white;
        }

        .action-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .action-icon svg {
            width: 36px;
            height: 36px;
            fill: #4CAF50;
            transition: all 0.3s ease;
        }

        .action-content h3 {
            font-size: 20px;
            color: #1e3a2c;
            font-weight: 600;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }

        .action-content p {
            color: #5a7a5f;
            font-size: 14px;
            line-height: 1.6;
            transition: color 0.3s ease;
        }

        .action-card:hover .action-content h3,
        .action-card:hover .action-content p {
            color: white;
        }

        /* NEW: Badge for pending count */
        .badge {
            display: inline-block;
            background: #FF9800;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }

        /* Users Table Section */
        .users-section {
            margin-top: 40px;
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .users-table thead {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        }

        .users-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #1e3a2c;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #e8f5e9;
            color: #5a7a5f;
        }

        .users-table tbody tr:hover {
            background: #f1f8f4;
        }

        .users-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* NEW: Star rating display */
        .star-rating {
            color: #FFD700;
            font-size: 16px;
            letter-spacing: 2px;
        }

        /* NEW: Comment preview */
        .comment-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* NEW: Action buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-approve {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            color: white;
        }

        .btn-approve:hover {
            background: linear-gradient(135deg, #45a049, #5cb85c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .btn-reject {
            background: linear-gradient(135deg, #f44336, #e57373);
            color: white;
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, #da190b, #d32f2f);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 30px;
            color: #5a7a5f;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .header-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .welcome-section {
                padding: 25px;
            }

            .welcome-content h2 {
                font-size: 24px;
            }

            .stats-grid,
            .action-grid {
                grid-template-columns: 1fr;
            }

            .users-table {
                font-size: 14px;
            }

            .users-table th,
            .users-table td {
                padding: 10px;
            }

            .comment-preview {
                max-width: 150px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <h1>Trail Admin</h1>
                    <p>Management Portal</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="manage_trails.php" class="nav-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                    </svg>
                    Manage Trails
                </a>
                <a href="logout.php" class="nav-link logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    Logout
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h2>🏔️ Welcome Back<?php if (isset($_SESSION['username'])): ?>, <?php echo htmlspecialchars($_SESSION['username']); ?><?php endif; ?>!</h2>
                <p>Your wilderness management hub. Monitor trails, explore data, and keep the adventure alive.</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>Total Trails</h3>
                        <div class="stat-number"><?php echo $total_trails; ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>Active Routes</h3>
                        <div class="stat-number"><?php echo $total_trails; ?></div>
                    </div>
                </div>
            </div>

            <!-- NEW: Pending Reviews Stat Card -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon pending">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>Pending Reviews</h3>
                        <div class="stat-number" style="color: #FF9800;"><?php echo $pending_reviews_count; ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>System Status</h3>
                        <div class="stat-number" style="font-size: 24px; color: #4CAF50;">Online</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-section">
            <h2 class="section-title">Quick Actions</h2>
            <div class="action-grid">
                <a href="manage_trails.php" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>Manage Trails</h3>
                        <p>Add, edit, or remove trail routes. Update difficulty levels and trail conditions.</p>
                    </div>
                </a>

                <!-- NEW: Review Management Action Card -->
                <a href="admin_reviews.php" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>Review Management<?php if ($pending_reviews_count > 0): ?><span class="badge"><?php echo $pending_reviews_count; ?></span><?php endif; ?></h3>
                        <p>Approve or reject user feedback and ratings to ensure appropriate content.</p>
                    </div>
                </a>

                <a href="view_analytics.php" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>View Analytics</h3>
                        <p>Track trail popularity, visitor data, and seasonal trends across all routes.</p>
                    </div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>User Management</h3>
                        <p>Oversee admin accounts and permissions for the trail management system.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- NEW: Pending Reviews Section -->
        <div class="users-section" id="pending-reviews">
            <h2 class="section-title">Pending Reviews</h2>
            <?php if ($pending_reviews): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Trail Name</th>
                            <th>Username</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_reviews as $review): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($review['trail_name']); ?></td>
                                <td><?php echo htmlspecialchars($review['username']); ?></td>
                                <td>
                                    <span class="star-rating">
                                        <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="comment-preview" title="<?php echo htmlspecialchars($review['comment']); ?>">
                                        <?php echo htmlspecialchars($review['comment']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($review['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" action="approve_review.php" style="display: inline;">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" class="btn btn-approve">✓ Approve</button>
                                        </form>
                                        <form method="POST" action="reject_review.php" style="display: inline;">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" class="btn btn-reject">✗ Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #5a7a5f; margin-top: 20px;">✓ No pending reviews at this time. All reviews have been processed!</p>
            <?php endif; ?>
        </div>

        <!-- Registered Users Section -->
        <div class="users-section">
            <h2 class="section-title">Registered Users</h2>
            <?php if ($users): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #5a7a5f; margin-top: 20px;">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>🌲 Trail Admin Dashboard • Protecting Nature, One Trail at a Time 🏔️</p>
    </footer>
</body>
</html>