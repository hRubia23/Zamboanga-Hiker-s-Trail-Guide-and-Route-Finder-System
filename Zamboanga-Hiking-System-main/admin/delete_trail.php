<?php
require_once "../includes/auth.php";
check_login();
require_once "../includes/db.php";
if (!isset($_GET['id'])) die("Trail ID is required!");
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM trails WHERE id=?");
$stmt->execute([$id]);
$trail = $stmt->fetch();
if (!$trail) die("Trail not found!");
if (isset($_POST['confirm'])) {
    if (!empty($trail['image'])) {
        $filePath = "../public/assets/uploads/" . $trail['image'];
        if (file_exists($filePath)) unlink($filePath);
    }
    $stmt = $pdo->prepare("DELETE FROM trails WHERE id=?");
    $stmt->execute([$id]);
    header("Location: manage_trails.php?deleted=1");
    exit();
}
?>
<h2>Delete Trail</h2>
<p>Are you sure you want to delete <strong><?= $trail['name'] ?></strong>?</p>
<form method="POST">
    <button type="submit" name="confirm">Yes, Delete</button>
    <a href="manage_trails.php">Cancel</a>
</form>
