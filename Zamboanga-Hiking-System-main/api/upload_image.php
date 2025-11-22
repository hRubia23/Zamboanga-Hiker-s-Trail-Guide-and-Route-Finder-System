<?php
header("Content-Type: application/json");
$targetDir = "../public/assets/uploads/";
if (!empty($_FILES['image']['name'])) {
    $fileName = basename($_FILES['image']['name']);
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        echo json_encode(["success"=>true,"file"=>$fileName]);
    } else echo json_encode(["success"=>false,"error"=>"Upload failed"]);
} else echo json_encode(["success"=>false,"error"=>"No file uploaded"]);
?>