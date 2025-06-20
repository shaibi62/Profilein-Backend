<?php
// send_otp_signup.php
include 'origins.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'dbConnect.php';  // make sure this sets up $conn correctly
header('Content-Type: application/json');
date_default_timezone_set('Asia/Karachi');

require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$data = json_decode(file_get_contents("php://input"), true);

// ✅ Validate input data
if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$name = mysqli_real_escape_string($conn, $data['name']);
$email = mysqli_real_escape_string($conn, $data['email']);
$password = password_hash($data['password'], PASSWORD_BCRYPT);

$checkFound = mysqli_query($conn, "SELECT * FROM tbluser WHERE email = '$email'");
if (mysqli_num_rows($checkFound) > 0) {
    echo json_encode(['success' => false, 'message' => 'User already exists']);
    exit;
}

// ✅ Check if already pending
$check = mysqli_query($conn, "SELECT * FROM tblpendinguser WHERE email = '$email'");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "DELETE FROM tblpendinguser WHERE email = '$email'");
}

// ✅ Generate OTP
$otp = rand(100000, 999999);
$expire_at = date("Y-m-d H:i:s", time() + 300); // 5 minutes

// ✅ Insert into tblpendinguser
$query = "INSERT INTO tblpendinguser (name, email, password, otp, expire_at)
          VALUES ('$name', '$email', '$password', '$otp', '$expire_at')";
if (!mysqli_query($conn, $query)) {
    echo json_encode(['success' => false, 'message' => 'Database insert failed']);
    exit;
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contact.profilein@gmail.com';
    $mail->Password   = 'qljw aqkc omhq tuls'; // Use App Password (NOT normal Gmail password)
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('contact.profilein@gmail.com', 'Profilein OTP');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';
    $mail->Body    = "Your OTP is <b>$otp</b>. It expires in 5 minutes.";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'OTP sent']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
?>
