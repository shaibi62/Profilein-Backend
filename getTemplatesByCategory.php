<?php

include 'origins.php';
header('Content-Type: application/json');

if (!isset($_GET['category']) || empty($_GET['category'])) {
    echo json_encode(['success' => false, 'error' => 'Category is required']);
    exit;
}

include 'dbConnect.php';

$category = mysqli_real_escape_string($conn, $_GET['category']);

$query = "SELECT * FROM tbltemplate WHERE category = '$category'";
$result = mysqli_query($conn, $query);

$response = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $response[] = $row;
    }
    echo json_encode(['success' => true, 'templates' => $response]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}
?>
