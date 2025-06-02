<?php
include 'origins.php';
include 'dbConnect.php';// get_users.php

$data = json_decode(file_get_contents("php://input"));

$title = $data->title;
$category = $data->category;
$feature1= $data->feature1;
$feature2 = $data->feature2;
$feature3= $data->feature3;
$imageUrl = $data->imageUrl;


$sql = "INSERT INTO template (Title, Category, Feature1, Feature2, Feature3, Image) VALUES (?, ?, ?, ?, ?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $title, $category, $feature1, $feature2, $feature3, $imageUrl);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$conn->close();
?>
