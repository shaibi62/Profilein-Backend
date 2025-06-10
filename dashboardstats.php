<?php

include 'origins.php';
include 'dbConnect.php';



// Fetch total users
$userResult = $conn->query("SELECT COUNT(*) as totalUsers FROM tbluser");
$userData = $userResult->fetch_assoc();

// Fetch total templates
$templateResult = $conn->query("SELECT COUNT(*) as totalTemplates FROM tbltemplate");
$templateData = $templateResult->fetch_assoc();

echo json_encode([
  'success' => true,
  'totalUsers' => $userData['totalUsers'],
  'totalTemplates' => $templateData['totalTemplates']
]);

$conn->close();
?>
