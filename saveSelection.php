<?php

include 'origins.php';
include 'dbConnect.php';
include 'injectData.php';
header("Content-Type: application/json");

$response = ['success' => false, 'error' => ''];

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Read raw JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (!isset($input['tempId']) || !isset($input['userId'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}


$tmpId = mysqli_real_escape_string($conn, $input['tempId']);
$usrId = mysqli_real_escape_string($conn, $input['userId']);
$genResult = injectData($usrId, $tmpId);


if (!$genResult['success']) {
    echo json_encode(['success' => false, 'error' => $genResult['error']]);
    exit;
}

$path = mysqli_real_escape_string($conn, $genResult['output_file']);

$query = "INSERT INTO tblportfolio (usrId, tmpId, portfolioLink) 
          VALUES ('$usrId', '$tmpId', '$path')
          ON DUPLICATE KEY UPDATE tmpId = '$tmpId'";

if (mysqli_query($conn, $query)) {
    $response = (['success' => true, 'message' => 'Portfolio saved successfully', 'path' => $path]);
  
} else {
    $response['error'] = mysqli_error($conn);
}

echo json_encode($response);
?>
