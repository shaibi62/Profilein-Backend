<?php
include 'origins.php';
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$userOtp = $data['otp'] ?? '';

if (!isset($_SESSION['otp'])) {
    echo json_encode(['status' => false, 'message' => 'OTP not found']);
    exit;
}

if (time() > $_SESSION['otp_expiry']) {
    unset($_SESSION['otp']);
    echo json_encode(['status' => false, 'message' => 'OTP expired']);
    exit;
}

if ($userOtp == $_SESSION['otp']) {
    unset($_SESSION['otp'], $_SESSION['otp_expiry']);
    echo json_encode(['status' => true, 'message' => 'OTP verified']);
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid OTP']);
}
