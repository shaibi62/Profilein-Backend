<?php
include 'origins.php';
header('Content-Type: application/json');

if (!isset($_GET['userId'])) {
    echo json_encode(['success' => false, 'error' => 'userId is required']);
    exit;
}

include 'dbConnect.php';

$userId = mysqli_real_escape_string($conn, $_GET['userId']);
$query = "SELECT * FROM tblpersonalinfo WHERE usrId = '$userId'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'User info not found']);
}
?>
