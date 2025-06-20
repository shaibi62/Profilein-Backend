<?php
include 'origins.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    exit(0);
}
include 'dbConnect.php';
include 'origins.php';
date_default_timezone_set('Asia/Karachi');

$data = json_decode(file_get_contents('php://input'), true);
$email = mysqli_real_escape_string($conn, $data['email']);
$otp = mysqli_real_escape_string($conn, $data['otp']);

$res = mysqli_query($conn,
  "SELECT * FROM tblpendinguser WHERE email='$email' AND otp='$otp' AND expire_at > NOW()");
if (mysqli_num_rows($res) === 0) {
  echo json_encode(['success'=>false,'message'=>'Invalid/expired OTP']);
  exit;
}

$user = mysqli_fetch_assoc($res);
$name = mysqli_real_escape_string($conn, $user['name']);
$pass = mysqli_real_escape_string($conn, $user['password']);

mysqli_query($conn, 
  "INSERT INTO tbluser (Name,Email,Password) VALUES ('$name','$email','$pass')");
mysqli_query($conn, "DELETE FROM tblpendinguser WHERE email='$email'");

echo json_encode(['success'=>true]);
