<?php
include 'origins.php';
include 'dbConnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents("php://input"));

  if (isset($data->id)) {
    $id = $data->id;

    // Fetch image path and folder name first
    $stmt = $conn->prepare("SELECT Image, Title FROM tbltemplate WHERE tmpId = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      $imagePath = 'C:/Users/dell/Desktop/ProfileIn/public/' . $row['Image'];
      $folderPath = 'C:/Users/dell/Desktop/ProfileIn/public/Templates/' . sanitizeFolderName($row['Title']) . '/';

      // Delete DB record
      $stmtDel = $conn->prepare("DELETE FROM tbltemplate WHERE tmpId = ?");
      $stmtDel->bind_param("i", $id);
      if ($stmtDel->execute()) {
        // Delete image file
        if (file_exists($imagePath)) unlink($imagePath);

        // Recursively delete folder
        function deleteFolder($folder) {
          if (!file_exists($folder)) return;
          $files = array_diff(scandir($folder), ['.', '..']);
          foreach ($files as $file) {
            $filePath = $folder . DIRECTORY_SEPARATOR . $file;
            is_dir($filePath) ? deleteFolder($filePath) : unlink($filePath);
          }
          rmdir($folder);
        }
        deleteFolder($folderPath);

        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
      }
      $stmtDel->close();
    } else {
      echo json_encode(['success' => false, 'message' => 'Template not found']);
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
  }

  $conn->close();
}

function sanitizeFolderName($string) {
  $string = trim($string);
  $string = preg_replace('/[^a-zA-Z0-9_-]/', '_', $string);
  $string = preg_replace('/_+/', '_', $string);
  return trim($string, '_') ?: 'template';
}
?>
