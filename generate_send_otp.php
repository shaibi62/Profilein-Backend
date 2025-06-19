<?php
// send_otp.php
include 'dbConnect.php';
header('Content-Type: application/json');

$email = $_POST['email'];
$type  = $_POST['type']; // 'signup' or 'reset'

// 1) Look up user (for signup you may create a stub row first)
$user = mysqli_fetch_assoc( mysqli_query($conn,
    "SELECT usrId FROM tbluser WHERE Email='" . mysqli_real_escape_string($conn,$email) . "'"
) );
if (!$user && $type==='reset') {
  echo json_encode(['success'=>false,'message'=>'No such email']);
  exit;
}

// 2) Generate 6‑digit OTP & expiry
$otp     = str_pad(rand(0,999999),6,'0',STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', time() + 5*60); // 5 minutes

// 3) Store in DB (replace old)
mysqli_query($conn,
  "REPLACE INTO tbl_otps (usrId,otp_code,expires_at,type)
   VALUES (
     " . intval($user['usrId'] ?? 0) . ",
     '$otp',
     '$expires',
     '$type'
   )"
);

// 4) Send it via email/SMS
// (Use PHPMailer, Twilio, etc. Here’s a sketch using PHP mail())
$subject = "Your OTP Code";
$message = "Your verification code is: $otp\nIt expires in 5 minutes.";
mail($email, $subject, $message);

echo json_encode(['success'=>true,'message'=>"OTP sent"]);
