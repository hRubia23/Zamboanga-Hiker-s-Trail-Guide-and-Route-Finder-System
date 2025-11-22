<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check admin login
check_login();

// Get report type
$type = isset($_GET['type']) ? $_GET['type'] : 'views';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

try {
    switch($type) {
        case 'views':
            // Trail Views Report
            fputcsv($output, ['Trail Name', 'Total Views', 'Logged-in Views', 'Guest Views', 'Last Viewed']);
            
            $stmt = $pdo->query("
                SELECT 
                    t.name,
                    COUNT(tv.id) as total_views,
                    SUM(CASE WHEN tv.user_id IS NOT NULL THEN 1 ELSE 0 END) as logged_in_views,
                    SUM(CASE WHEN tv.user_id IS NULL THEN 1 ELSE 0 END) as guest_views,
                    MAX(tv.viewed_at) as last_viewed
                FROM trails t
                LEFT JOIN trail_views tv ON t.id = tv.trail_id
                GROUP BY t.id, t.name
                ORDER BY total_views DESC
            ");
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    $row['name'],
                    $row['total_views'] ?? 0,
                    $row['logged_in_views'] ?? 0,
                    $row['guest_views'] ?? 0,
                    $row['last_viewed'] ?? 'Never'
                ]);
            }
            break;
            
        case 'users':
            // Users Report
            fputcsv($output, ['Username', 'First Name', 'Last Name', 'Email', 'Registration Date', 'Total Reviews']);
            
            $stmt = $pdo->query("
                SELECT 
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.created_at,
                    COUNT(r.id) as review_count
                FROM users u
                LEFT JOIN reviews r ON u.id = r.user_id
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ");
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    $row['username'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['email'],
                    $row['created_at'],
                    $row['review_count'] ?? 0
                ]);
            }
            break;
            
        case 'trails':
            // Trails Report
            fputcsv($output, ['Trail Name', 'Difficulty', 'Distance (km)', 'Duration (hrs)', 'Elevation (m)', 'Total Views', 'Avg Rating', 'Total Reviews']);
            
            $stmt = $pdo->query("
                SELECT 
                    t.name,
                    t.difficulty,
                    t.distance,
                    t.duration,
                    t.elevation,
                    COUNT(DISTINCT tv.id) as total_views,
                    AVG(r.rating) as avg_rating,
                    COUNT(DISTINCT r.id) as review_count
                FROM trails t
                LEFT JOIN trail_views tv ON t.id = tv.trail_id
                LEFT JOIN reviews r ON t.id = r.trail_id AND r.status = 'approved'
                GROUP BY t.id
                ORDER BY t.name
            ");
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    $row['name'],
                    $row['difficulty'] ?? 'N/A',
                    $row['distance'] ?? 'N/A',
                    $row['duration'] ?? 'N/A',
                    $row['elevation'] ?? 'N/A',
                    $row['total_views'] ?? 0,
                    $row['avg_rating'] ? number_format($row['avg_rating'], 2) : 'N/A',
                    $row['review_count'] ?? 0
                ]);
            }
            break;
            
        default:
            fputcsv($output, ['Error: Invalid report type']);
    }
} catch(PDOException $e) {
    fputcsv($output, ['Error generating report: ' . $e->getMessage()]);
}

fclose($output);
exit();
?>