<?php
include 'origins.php';
include 'dbConnect.php';
$data = json_decode(file_get_contents('php://input'), true);

if (
    isset($data['Template_ID']) &&
    isset($data['Title']) &&
    isset($data['Category']) &&
    isset($data['Feature1']) &&
    isset($data['Feature2']) &&
    isset($data['Feature3']) &&
    isset($data['ImageUrl']) 
) {
    $id = intval($data['Template_ID']);
    $title = mysqli_real_escape_string($conn, $data['Title']);
    $category = mysqli_real_escape_string($conn, $data['Category']);
    $feature1 = mysqli_real_escape_string($conn, $data['Feature1']);
    $feature2 = mysqli_real_escape_string($conn, $data['Feature2']);
    $feature3 = mysqli_real_escape_string($conn, $data['Feature3']);
    $imageUrl = mysqli_real_escape_string($conn, $data['ImageUrl']);


    $query = "UPDATE templates SET 
        Title = '$title',
        Category = '$category',
        Feature1= '$feature1',
        Feature2= '$feature2',
        Feature3= '$feature3',
        ImageUrl = '$imageUrl',
        
        WHERE Template_ID = $id";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update template']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}

mysqli_close($conn);
?>
