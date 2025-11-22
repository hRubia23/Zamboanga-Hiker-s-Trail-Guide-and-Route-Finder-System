<?php
require_once 'db.php';

// Delete verification codes older than 1 hour
$stmt = $pdo->prepare("
    DELETE FROM verification_codes 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
");
$stmt->execute();

echo "Cleanup completed. Deleted " . $stmt->rowCount() . " expired codes.";
?>