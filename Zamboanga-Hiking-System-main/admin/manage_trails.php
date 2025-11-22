<?php
require_once "../includes/auth.php";
check_login();
require_once "../includes/db.php";

$trails = $pdo->query("SELECT * FROM trails")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trail Admin - Manage Trails</title>
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

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .title-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .title-icon svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        .page-title h2 {
            font-family: 'Merriweather', serif;
            font-size: 32px;
            color: #1e3a2c;
        }

        .add-trail-btn {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .add-trail-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);
        }

        .add-trail-btn svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        /* Success Message */
        .success-message {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            color: #2e7d32;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid #4CAF50;
            animation: slideIn 0.4s ease-out;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.15);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .success-message::before {
            content: '✓';
            font-size: 24px;
            font-weight: bold;
            width: 32px;
            height: 32px;
            background: #4CAF50;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #1e3a2c, #2d5a3f);
        }

        thead th {
            color: white;
            padding: 20px 24px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f5f5f5;
            transform: scale(1.01);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody td {
            padding: 20px 24px;
            color: #333;
            font-size: 14px;
        }

        tbody td:first-child {
            font-weight: 600;
            color: #1e3a2c;
        }

        /* Difficulty Badges */
        .difficulty-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .difficulty-badge.easy {
            background: linear-gradient(135deg, #c8e6c9, #a5d6a7);
            color: #2e7d32;
        }

        .difficulty-badge.moderate {
            background: linear-gradient(135deg, #fff9c4, #fff59d);
            color: #f57f17;
        }

        .difficulty-badge.hard {
            background: linear-gradient(135deg, #ffccbc, #ffab91);
            color: #d84315;
        }

        .difficulty-badge.extreme {
            background: linear-gradient(135deg, #f8bbd0, #f48fb1);
            color: #c2185b;
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn svg {
            width: 16px;
            height: 16px;
        }

        .action-btn.edit {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1565c0;
            border: 1px solid #90caf9;
        }

        .action-btn.edit:hover {
            background: linear-gradient(135deg, #bbdefb, #90caf9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .action-btn.delete {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .action-btn.delete:hover {
            background: linear-gradient(135deg, #ffcdd2, #ef9a9a);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7a9d7e;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            fill: #c8e6c9;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #1e3a2c;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 25px;
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
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }

            .actions {
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
                <a href="dashboard.php" class="nav-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    Dashboard
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
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <div class="title-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                    </svg>
                </div>
                <h2>Manage Trails</h2>
            </div>
            <a href="add_trail.php" class="add-trail-btn">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Add New Trail
            </a>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['deleted'])): ?>
            <div class="success-message">
                Trail deleted successfully!
            </div>
        <?php endif; ?>

        <!-- Trails Table -->
        <div class="table-container">
            <?php if (count($trails) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Trail Name</th>
                            <th>Location</th>
                            <th>Difficulty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trails as $trail): ?>
                            <tr>
                                <td><?= htmlspecialchars($trail['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($trail['location'] ?? '') ?></td>
                                <td>
                                    <span class="difficulty-badge <?= strtolower($trail['difficulty'] ?? '') ?>">
                                        <?= htmlspecialchars($trail['difficulty'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="edit_trail.php?id=<?= $trail['id'] ?>" class="action-btn edit">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="delete_trail.php?id=<?= $trail['id'] ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this trail?');">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                            </svg>
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                    </svg>
                    <h3>No Trails Found</h3>
                    <p>Start by adding your first trail to the system.</p>
                    <a href="add_trail.php" class="add-trail-btn" style="display: inline-flex;">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                        Add Your First Trail
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>🌲 Trail Admin Dashboard • Protecting Nature, One Trail at a Time 🏔️</p>
    </footer>
</body>
</html>