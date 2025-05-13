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

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        setcookie('access_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        echo json_encode(["success" => true, "message" => "Logged out"]);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        break;
}
?>