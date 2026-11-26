<?php

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

if (!isset($_GET['trail_id']) || empty($_GET['trail_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Trail ID is required'
    ]);
    exit;
}

$trail_id = intval($_GET['trail_id']);
$current_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

try {
    // Approved reviews are public; a user also sees their own pending reviews
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.rating,
            r.comment,
            r.status,
            r.created_at,
            u.username
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.trail_id = ?
          AND (r.status = 'approved' OR (r.status = 'pending' AND r.user_id = ?))
        ORDER BY r.created_at DESC
    ");
    
    $stmt->execute([$trail_id, $current_user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Rating summary counts approved reviews only
    $summary = $pdo->prepare("SELECT COUNT(*) AS total, AVG(rating) AS avg_rating FROM reviews WHERE trail_id = ? AND status = 'approved'");
    $summary->execute([$trail_id]);
    $s = $summary->fetch(PDO::FETCH_ASSOC);
    $count = (int)$s['total'];
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'rating_count' => $count,
        'rating_average' => $count > 0 ? round((float)$s['avg_rating'], 1) : null
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
exit;
?>