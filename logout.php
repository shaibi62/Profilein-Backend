<?php
require_once __DIR__ . '/vendor/autoload.php';

    include 'origins.php';
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
            'secure' => false,
            'httponly' => true,
            'samesite' => 'LAX'
        ]);

        echo json_encode(["success" => true, "message" => "Logged out"]);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        break;
}
?>