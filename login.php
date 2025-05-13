<?php
    require_once __DIR__ . '/vendor/autoload.php';

    // CORS Headers (Important: Must be at the top of EVERY PHP file)
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    include 'dbConnect.php';
    include 'genJwt.php';

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            $email = $data->email ?? '';
            $pass = $data->password ?? '';

            if (empty($email) || empty($pass)) {
                echo json_encode(["success" => false, "message" => "Email and password are required"]);
                exit;
            }

            // Look up user by email
            $sql = "SELECT * FROM user WHERE Email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                // Email found, now verify password
                if (password_verify($pass, $user['Password'])) {
                    genJwt($user['User_ID'], $user['Email']);
                    echo json_encode([
                        "success" => true,
                        "message" => $user['Name'] . " Welcome to ProfileIn",
                        "data" => $user
                    ]);
                } else {
                    echo json_encode(["success" => false, "message" => "Incorrect password"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Email not found"]);
            }
            break;
    }
    ?>