<?php
require_once "../includes/db.php";
header("Content-Type: application/json");
$stmt = $pdo->query("SELECT * FROM trails");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>