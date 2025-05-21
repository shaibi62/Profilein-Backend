<?php

include 'origins.php';
include 'dbConnect.php';// get_users.php


$query = "SELECT User_ID, Name, Email FROM user";
$result = mysqli_query($conn, $query);

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
  $users[] = $row;
}

echo json_encode([
  'success' => true,
  'users' => $users
]);
?>