<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
        $stmt->execute([$review_id]);
        
        // Redirect back to dashboard with success message
        header('Location: dashboard.php#pending-reviews');
        exit;
    } catch (PDOException $e) {
        // Redirect back with error
        header('Location: dashboard.php?error=approve_failed');
        exit;
    }
} else {
    // Invalid request
    header('Location: dashboard.php');
    exit;
}
?>