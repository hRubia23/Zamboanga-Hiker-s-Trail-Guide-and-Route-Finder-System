<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$review_id]);
        
        
        header('Location: dashboard.php#pending-reviews');
        exit;
    } catch (PDOException $e) {
        
        header('Location: dashboard.php?error=reject_failed');
        exit;
    }
} else {
    
    header('Location: dashboard.php');
    exit;
}
?>