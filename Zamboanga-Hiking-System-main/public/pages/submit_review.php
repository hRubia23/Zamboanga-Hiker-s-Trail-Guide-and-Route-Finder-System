<?php
session_start();
header('Content-Type: application/json');
require_once '../../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'You must be logged in to submit a review'
    ]);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Try to get from $_POST if JSON decode fails
    $data = $_POST;
}

$trail_id = $data['trail_id'] ?? null;
$rating = $data['rating'] ?? null;
$comment = $data['comment'] ?? null;
$user_id = $_SESSION['user_id'];

// Validate inputs
if (!$trail_id || !$rating || !$comment) {
    echo json_encode([
        'success' => false,
        'error' => 'All fields are required'
    ]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode([
        'success' => false,
        'error' => 'Rating must be between 1 and 5'
    ]);
    exit;
}

try {
    // Insert review with 'pending' status
    $stmt = $pdo->prepare("
        INSERT INTO reviews (trail_id, user_id, rating, comment, status, created_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([$trail_id, $user_id, $rating, $comment]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your review is pending approval and will be visible after admin verification.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to submit review. Please try again later.'
    ]);
}
?>