<?php
// Clean output buffer to prevent any extra characters
ob_clean();

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/db.php';

if (!isset($_GET['trail_id']) || empty($_GET['trail_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Trail ID is required'
    ]);
    exit;
}

$trail_id = intval($_GET['trail_id']);

try {
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.rating,
            r.comment,
            r.created_at,
            u.username
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.trail_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC
    ");
    
    $stmt->execute([$trail_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
exit;
?>