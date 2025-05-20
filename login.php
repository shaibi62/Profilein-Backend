<?php
    require_once __DIR__ . '/vendor/autoload.php';


    include 'origins.php';

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
                    if(genJwt($user['User_ID'], $user['Name'], $user['Email'])){
                    echo json_encode([
                        "success" => true,
                        "message" => $user['Name'] . " Welcome to ProfileIn",
                        "data" => $user
                    ]);
                }
                } else {
                    echo json_encode(["success" => false, "message" => "Incorrect password"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Email not found"]);
            }
            break;
    }
    ?>