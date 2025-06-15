<?php
include 'origins.php';
include 'dbConnect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        try {
            $data = json_decode(file_get_contents("php://input"));
            $userId = $data->userId ?? '';
            $Star = $data->Star ?? '';
            $Message = $data->Message ?? '';

            if (trim($Message) === '') {
                echo json_encode(["success" => false, "message" => "Message can't be empty"]);
                exit;
            }

            $sql = "INSERT INTO tblfeedback (usrId, Star, Content)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE Star = VALUES(Star), Content = VALUES(Content)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $userId, $Star, $Message);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Submitted successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "No changes made"]);
            }

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Unexpected error: " . $e->getMessage(),
                "code" => "SERVER_ERROR"
            ]);
        }
        break;
}
?>
