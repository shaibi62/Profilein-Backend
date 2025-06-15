<?php
include 'origins.php';
include 'dbConnect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        try {
            $data = json_decode(file_get_contents("php://input"));
            $Name = $data->Name ?? '';
            $Email = $data->Email ?? '';
            $Message = $data->Message ?? '';

            if (trim($Message) === '') {
                echo json_encode(["success" => false, "message" => "Message can't be empty"]);
                exit;
            }

            $sql = "INSERT INTO tblcontact (Name, Email, Message) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $Name, $Email, $Message);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Submitted successfully"]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to insert data",
                ]);
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
