<?php

include 'origins.php';
include 'dbConnect.php';// get_users.php


$sql = "SELECT Template_ID, Title, Category FROM template";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $templates = [];
    while($row = $result->fetch_assoc()) {
        $templates[] = $row;
    }
    echo json_encode(["success" => true, "templates" => $templates]);
} else {
    echo json_encode(["success" => true, "templates" => []]);
}

$conn->close();
?>
