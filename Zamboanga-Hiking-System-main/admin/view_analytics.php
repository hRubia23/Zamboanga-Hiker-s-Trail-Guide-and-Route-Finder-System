<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check admin login
check_login();

// Fetch analytics data

// 1. Total Counts
$stmt = $pdo->query("SELECT COUNT(*) as count FROM trail_views");
$total_views = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM trails");
$total_trails = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'approved'");
$total_reviews = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// 2. Views by logged-in users vs guests
$stmt = $pdo->query("SELECT COUNT(*) as count FROM trail_views WHERE user_id IS NOT NULL");
$logged_in_views = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$guest_views = $total_views - $logged_in_views;

// 3. Most Popular Trails (Top 5)
$stmt = $pdo->query("
    SELECT t.name, COUNT(tv.id) as view_count
    FROM trails t
    LEFT JOIN trail_views tv ON t.id = tv.trail_id
    GROUP BY t.id, t.name
    ORDER BY view_count DESC
    LIMIT 5
");
$popular_trails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Recent Trail Views (Last 7 days)
$stmt = $pdo->query("
    SELECT DATE(viewed_at) as date, COUNT(*) as count
    FROM trail_views
    WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(viewed_at)
    ORDER BY date ASC
");
$weekly_views = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. User Registration Trend (Last 30 days)
$stmt = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$user_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Reviews per Trail
$stmt = $pdo->query("
    SELECT t.name, COUNT(r.id) as review_count
    FROM trails t
    LEFT JOIN reviews r ON t.id = r.trail_id AND r.status = 'approved'
    GROUP BY t.id, t.name
    ORDER BY review_count DESC
    LIMIT 5
");
$trail_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 7. Average Rating per Trail
$stmt = $pdo->query("
    SELECT t.name, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
    FROM trails t
    LEFT JOIN reviews r ON t.id = r.trail_id AND r.status = 'approved'
    GROUP BY t.id, t.name
    HAVING review_count > 0
    ORDER BY avg_rating DESC
    LIMIT 5
");
$top_rated_trails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JavaScript Charts
$popular_trails_labels = json_encode(array_column($popular_trails, 'name'));
$popular_trails_data = json_encode(array_column($popular_trails, 'view_count'));

$weekly_labels = json_encode(array_column($weekly_views, 'date'));
$weekly_data = json_encode(array_column($weekly_views, 'count'));

$registration_labels = json_encode(array_column($user_registrations, 'date'));
$registration_data = json_encode(array_column($user_registrations, 'count'));

$trail_reviews_labels = json_encode(array_column($trail_reviews, 'name'));
$trail_reviews_data = json_encode(array_column($trail_reviews, 'review_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Analytics - Trail Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .page-header {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .page-header h2 {
            font-family: 'Merriweather', serif;
            font-size: 32px;
            color: #1e3a2c;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #5a7a5f;
            font-size: 16px;
        }

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

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .chart-title {
            font-family: 'Merriweather', serif;
            font-size: 20px;
            color: #1e3a2c;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .top-trails-list {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .trail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e8f5e9;
            transition: all 0.3s ease;
        }

        .trail-item:hover {
            background: #f1f8f4;
            transform: translateX(5px);
        }

        .trail-item:last-child {
            border-bottom: none;
        }

        .trail-name {
            font-weight: 600;
            color: #1e3a2c;
            font-size: 16px;
        }

        .trail-stat {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .trail-badge {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
        }

        .rating-badge {
            background: linear-gradient(135deg, #FFB74D, #FFA726);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .download-section {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .btn-download {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            color: white;
            padding: 15px 35px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-download:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <h1>Trail Analytics</h1>
                    <p>Data & Insights</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="dashboard.php" class="nav-link">
                    ← Back to Dashboard
                </a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h2>📊 Analytics & Reports</h2>
            <p>Track system performance, user engagement, and trail popularity metrics</p>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>Total Views</h3>
                        <div class="stat-number"><?php echo number_format($total_views); ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>Total Users</h3>
                        <div class="stat-number"><?php echo number_format($total_users); ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>Total Trails</h3>
                        <div class="stat-number"><?php echo number_format($total_trails); ?></div>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/>
                        </svg>
                    </div>
                    <div class="stat-details">
                        <h3>Total Reviews</h3>
                        <div class="stat-number"><?php echo number_format($total_reviews); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3 class="chart-title">Trail Views (Last 7 Days)</h3>
                <canvas id="weeklyViewsChart"></canvas>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">User Registrations (Last 30 Days)</h3>
                <canvas id="registrationsChart"></canvas>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">Most Popular Trails</h3>
                <canvas id="popularTrailsChart"></canvas>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">Visitor Types</h3>
                <canvas id="visitorTypesChart"></canvas>
            </div>
        </div>

        <!-- Top Rated Trails -->
        <div class="top-trails-list">
            <h3 class="chart-title">⭐ Top Rated Trails</h3>
            <?php foreach($top_rated_trails as $trail): ?>
                <div class="trail-item">
                    <span class="trail-name"><?php echo htmlspecialchars($trail['name']); ?></span>
                    <div class="trail-stat">
                        <span class="rating-badge">
                            ⭐ <?php echo number_format($trail['avg_rating'], 1); ?>
                        </span>
                        <span style="color: #7a9d7e; font-size: 14px;">
                            (<?php echo $trail['review_count']; ?> reviews)
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Download Reports -->
        <div class="download-section">
            <h3 class="chart-title">📥 Download Reports</h3>
            <p style="color: #5a7a5f; margin-bottom: 20px;">Export analytics data for further analysis</p>
            <a href="export_report.php?type=views" class="btn-download">Download Views Report (CSV)</a>
            <a href="export_report.php?type=users" class="btn-download">Download Users Report (CSV)</a>
            <a href="export_report.php?type=trails" class="btn-download">Download Trails Report (CSV)</a>
        </div>
    </div>

    <script>
        // Weekly Views Chart
        const weeklyCtx = document.getElementById('weeklyViewsChart').getContext('2d');
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: <?php echo $weekly_labels; ?>,
                datasets: [{
                    label: 'Views',
                    data: <?php echo $weekly_data; ?>,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // User Registrations Chart
        const regCtx = document.getElementById('registrationsChart').getContext('2d');
        new Chart(regCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $registration_labels; ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo $registration_data; ?>,
                    backgroundColor: '#66BB6A',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Popular Trails Chart
        const popularCtx = document.getElementById('popularTrailsChart').getContext('2d');
        new Chart(popularCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $popular_trails_labels; ?>,
                datasets: [{
                    label: 'Views',
                    data: <?php echo $popular_trails_data; ?>,
                    backgroundColor: '#4CAF50',
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Visitor Types Pie Chart
        const visitorCtx = document.getElementById('visitorTypesChart').getContext('2d');
        new Chart(visitorCtx, {
            type: 'doughnut',
            data: {
                labels: ['Logged-in Users', 'Guest Visitors'],
                datasets: [{
                    data: [<?php echo $logged_in_views; ?>, <?php echo $guest_views; ?>],
                    backgroundColor: ['#4CAF50', '#81C784'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>