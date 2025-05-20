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
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);


switch ($method) {
    case 'POST':
        setcookie('access_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $is_https,
            'httponly' => true,
            'samesite' => $is_https ? 'None' : 'Lax'
        ]);

        echo json_encode(["success" => true, "message" => "Logged out"]);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        break;
}
?>