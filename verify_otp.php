<?php
include 'dbConnect.php';
include 'origins.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$otp = $data['otp'];

$query = "SELECT * FROM otp_verification 
          WHERE email = ? AND otp = ? AND is_verified = 0 AND created_at > NOW() - INTERVAL 5 MINUTE";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $update = $conn->prepare("UPDATE otp_verification SET is_verified = 1 WHERE email = ? AND otp = ?");
    $update->bind_param("ss", $email, $otp);
    $update->execute();

    echo json_encode(['success' => true, 'message' => 'OTP verified']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
}
?>
