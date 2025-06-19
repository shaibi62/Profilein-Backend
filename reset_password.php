<?php
header('Content-Type: application/json');
include 'origins.php';
include 'dbConnect.php';

// Get JSON data from frontend
$data = json_decode(file_get_contents("php://input"), true);

// Check if email and new password are set
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$email = mysqli_real_escape_string($conn, $data['email']);
$password = mysqli_real_escape_string($conn, $data['password']);

// Hash the new password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Check if email exists
$checkQuery = "SELECT * FROM tbluser WHERE Email = '$email'";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    // Update password
    $updateQuery = "UPDATE tbluser SET Password = '$hashedPassword' WHERE Email = '$email'";
    if (mysqli_query($conn, $updateQuery)) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email not found']);
}

mysqli_close($conn);
?>
