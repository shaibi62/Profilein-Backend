<?php
include 'origins.php';
include 'dbConnect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try{

            $sql = "SELECT Star, Content FROM tblfeedback";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $feedback[] = $row;
                }
                echo json_encode(["success" => true, "data" => $feedback]);
            } else {
                echo json_encode(["success" => false, "message" => "No feedback found for this user"]);
            }

        }
        catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Unexpected error: " . $e->getMessage(),
                "code" => "SERVER_ERROR"
            ]);
        }
        break;}    

        
?>        