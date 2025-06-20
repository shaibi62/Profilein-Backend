<?php
include 'origins.php';
session_start();
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$userEmail = $data['email'] ?? "mshoaibarid@gmail.com";
if (!$userEmail) {
    echo json_encode(['status' => false, 'message' => 'Email is required']);
    exit;
}

$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 300;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact.profilein@gmail.com';
    $mail->Password = 'qljw aqkc omhq tuls';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('contact.profilein@gmail.com', 'Gmail');
    $mail->addAddress($userEmail);
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';
    $mail->Body = "Your OTP is <b>$otp</b>. It expires in 5 minutes.";

    $mail->send();
    echo json_encode(['status' => true, 'message' => 'OTP sent']);
} catch (Exception $e) {
    echo json_encode(['status' => false,  'message' => 'Error sending email: ' . $mail->ErrorInfo]);
}
