<?php

include 'origins.php';
include 'dbConnect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM template";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $templates = [];
            while ($row = $result->fetch_assoc()) {
                $templates[] = $row;
            }
            echo json_encode(["success" => true, "templates" => $templates]);
        } else {
            echo json_encode(["success" => false, "message" => "No templates found"]);
        }
        break;
    }

?>