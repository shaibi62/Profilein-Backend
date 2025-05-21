<?php

include 'origins.php';
include 'dbConnect.php';



// Fetch total users
$userResult = $conn->query("SELECT COUNT(*) as totalUsers FROM user");
$userData = $userResult->fetch_assoc();

// Fetch total templates
$templateResult = $conn->query("SELECT COUNT(*) as totalTemplates FROM template");
$templateData = $templateResult->fetch_assoc();

echo json_encode([
  'success' => true,
  'totalUsers' => $userData['totalUsers'],
  'totalTemplates' => $templateData['totalTemplates']
]);

$conn->close();
?>
