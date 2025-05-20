<?php

include 'origins.php';
include 'dbConnect.php';
include 'genJwt.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        try {
            $data = json_decode(file_get_contents("php://input"));
            
            $name = trim($data->name ?? '');
            $email = trim($data->email ?? '');
            $passRaw = $data->password ?? '';

            // Validate required fields
            if (empty($name) || empty($email) || empty($passRaw)) {
                echo json_encode([
                    "success" => false,
                    "message" => "Missing required fields",
                    "code" => "MISSING_FIELDS"
                ]);
                exit;
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    "success" => false,
                    "message" => "Invalid email format",
                    "code" => "INVALID_EMAIL"
                ]);
                exit;
            }

            // Hash password securely
            $pass = password_hash($passRaw, PASSWORD_DEFAULT);

            // Prepare and execute insert query
            $sql = "INSERT INTO user (Name, Email, Password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: " . $conn->error);
            }
            $stmt->bind_param("sss", $name, $email, $pass);

            if ($stmt->execute()) {
                $sql = "SELECT * FROM user WHERE Email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($user = $result->fetch_assoc()) {                
                    genJwt($user['User_ID'], $user['Name'], $user['Email']);
                    echo json_encode(["success" => true, "message" => "User created successfully"]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "User not found after creation",
                        "code" => "USER_NOT_FOUND"
                    ]);
                }
            } else {
                // Handle duplicate email or other DB errors
                if ($conn->errno === 1062) { // MySQL duplicate entry
                    echo json_encode([
                        "success" => false,
                        "message" => "Email already exists",
                        "code" => "EMAIL_EXISTS"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Database error: " . $stmt->error,
                        "code" => "DB_ERROR"
                    ]);
                }
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
