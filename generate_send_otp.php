<?php
include 'origins.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
include 'dbConnect.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Karachi');

require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? null;
$type  = $input['type'] ?? null; // 'signup' or 'reset'

// 1) Check if user exists (for reset)
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT usrId FROM tbluser WHERE Email='" . mysqli_real_escape_string($conn, $email) . "'"
));
if (!$user && $type === 'reset') {
    echo json_encode(['success' => false, 'message' => 'No such email']);
    exit;
}

// 2) Generate OTP
$otp     = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', time() + 5 * 60); // 5 mins

// 3) Save OTP
mysqli_query($conn,
    "REPLACE INTO tbl_otps (usrId, otp_code, expires_at, type)
     VALUES (
         " . intval($user['usrId'] ?? 0) . ",
         '$otp',
         '$expires',
         '$type'
     )"
);

// 4) Send Email
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
