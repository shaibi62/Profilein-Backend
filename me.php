<?php
    require_once __DIR__ . '/vendor/autoload.php';

     include 'origins.php';
     
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    use \Firebase\JWT\JWT;
    use \Firebase\JWT\Key;
    
    $token = $_COOKIE['access_token'] ?? '';

    if (!$token) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Not logged in"]);
    }
    else{
        try {
            $secretKey = 'ZHS@FYP!SECURITY!';
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            echo json_encode([
                "success" => true,
                "user" => [
                    "id" => $decoded->id,
                    "name" => $decoded->name,
                    "email" => $decoded->email

                ]
            ]);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid or expired token"]);
        }
    }

    ?>