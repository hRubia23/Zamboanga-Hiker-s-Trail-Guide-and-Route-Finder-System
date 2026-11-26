<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

check_login();

$stmt = $pdo->query("
    SELECT id, username, first_name, last_name, email, is_verified, created_at
    FROM users
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Trail Admin</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e3a2c;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #1e3a2c, #2d5a3f);
            color: white;
            padding: 18px 24px;
        }
        .header-content, main {
            width: min(1100px, calc(100% - 32px));
            margin: 0 auto;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .header h1 { margin: 0; font-size: 22px; }
        .header nav { display: flex; gap: 12px; }
        .header a { color: white; text-decoration: none; }
        .header a:hover { text-decoration: underline; }
        main { padding: 36px 0; }
        .panel {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 8px 26px rgba(30, 58, 44, .1);
        }
        .panel-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        h2 { margin: 0 0 6px; font-size: 26px; }
        .muted { color: #66806b; margin: 0; }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }
        input[type="search"] {
            width: min(360px, 100%);
            padding: 11px 14px;
            border: 1px solid #cbd9ce;
            border-radius: 8px;
            font: inherit;
        }
        .export-link {
            color: #237a3b;
            font-weight: 600;
            white-space: nowrap;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 720px; }
        th, td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #e6eee7;
        }
        th {
            background: #e8f5e9;
            color: #2d5a3f;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .verified { background: #e1f3e5; color: #237a3b; }
        .unverified { background: #fff3d6; color: #8a6413; }
        .empty { padding: 32px; text-align: center; color: #66806b; }
        @media (max-width: 600px) {
            .header-content, .panel-heading, .toolbar { align-items: flex-start; flex-direction: column; }
            .panel { padding: 20px 16px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>Trail Admin</h1>
            <nav aria-label="Admin navigation">
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>User Management</h2>
                    <p class="muted"><?= count($users) ?> registered account<?= count($users) === 1 ? '' : 's' ?></p>
                </div>
            </div>
            <div class="toolbar">
                <input type="search" id="user-search" placeholder="Search username, name, or email" aria-label="Search users">
                <a class="export-link" href="export_report.php?type=users">Download user report</a>
            </div>
            <?php if ($users): ?>
                <div class="table-wrap">
                    <table id="users-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if (!empty($user['is_verified'])): ?>
                                            <span class="status verified">Verified</span>
                                        <?php else: ?>
                                            <span class="status unverified">Unverified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="empty" id="no-results" hidden>No matching users found.</p>
            <?php else: ?>
                <p class="empty">No registered users yet.</p>
            <?php endif; ?>
        </section>
    </main>
    <script>
        const searchInput = document.getElementById('user-search');
        const userRows = document.querySelectorAll('#users-table tbody tr');
        const noResults = document.getElementById('no-results');

        if (searchInput && userRows.length && noResults) {
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();
                let visibleRows = 0;
                userRows.forEach((row) => {
                    const matches = row.textContent.toLowerCase().includes(query);
                    row.hidden = !matches;
                    if (matches) visibleRows++;
                });
                noResults.hidden = visibleRows !== 0;
            });
        } else if (searchInput) {
            searchInput.disabled = true;
        }
    </script>
</body>
</html>
