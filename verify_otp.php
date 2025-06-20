<?php
include 'dbConnect.php';
include 'origins.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$otp = $data['otp'];
$type = $data['type'] ?? 'reset';

// Step 1: Get usrId from email
$stmt = $conn->prepare("SELECT usrId FROM tbluser WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No such user']);
    exit;
}
$user = $result->fetch_assoc();
$usrId = $user['usrId'];

// Step 2: Check OTP match and not expired
$stmt = $conn->prepare("SELECT * FROM tbl_otps 
    WHERE usrId = ? AND otp_code = ? AND type = ? AND expires_at > NOW()");
$stmt->bind_param("iss", $usrId, $otp, $type);
$stmt->execute();
$otpResult = $stmt->get_result();

if ($otpResult->num_rows > 0) {
    // Optional: delete or mark OTP as used here if needed
    echo json_encode(['success' => true, 'message' => 'OTP verified']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
}
?>
