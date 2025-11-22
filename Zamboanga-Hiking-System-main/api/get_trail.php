<?php
require_once "../includes/db.php";
header("Content-Type: application/json");
if (!isset($_GET['id'])) { echo json_encode(["error" => "Trail ID required"]); exit(); }
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM trails WHERE id=?");
$stmt->execute([$id]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: ["error"=>"Not found"]);
?>