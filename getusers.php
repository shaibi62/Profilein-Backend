<?php

include 'origins.php';
include 'dbConnect.php';// get_users.php


$query = "SELECT usrId, Name, Email FROM tbluser";
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