<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
require_once __DIR__ . '/vendor/autoload.php';

function genJwt($id,$name , $email) {
    $secretKey = 'ZHS@FYP!SECURITY!';
    $payload = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'exp' => time() + (60 * 60 * 24 * 7)  // 7 days
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

setcookie('access_token', $jwt, [
    'expires' => time() + (60 * 60 * 24 * 7), // 7 days
    'path' => '/',
    'secure' => $is_https,
    'httponly' => true,
    'samesite' => $is_https ? 'None' : 'Lax'
]);
    return $jwt;
}

?>