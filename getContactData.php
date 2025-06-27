<?php
include 'origins.php';
include 'dbConnect.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $sql = "SELECT * FROM tblcontact";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            $ContactData = [];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ContactData[] = $row;
                }
                echo json_encode(["success" => true, "Contacts" => $ContactData]);
            } else {
                echo json_encode(["success" => false, "message" => "No contact data found."]);
            }

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Unexpected error: " . $e->getMessage(),
                "code" => "SERVER_ERROR"
            ]);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            "success" => false,
            "message" => "Method not allowed"
        ]);
        break;
}
?>
