<?php
include 'origins.php';
include 'dbConnect.php';// get_users.php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents("php://input"));

  if (isset($data->id)) {
    $id = $data->id;

    $sql = "DELETE FROM template WHERE Template_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Delete failed']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
  }
}
?>
