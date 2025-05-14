<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
require_once __DIR__ . '/vendor/autoload.php';

function genJwt($id, $email) {
    $secretKey = 'ZHS@FYP!SECURITY!';
    $payload = [
        'id' => $id,
        'email' => $email,
        'exp' => time() + (60 * 60 * 24 * 7)  // 7 days
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    setcookie('access_token', $jwt, [
        'expires' => time() + (60 * 60 * 24 * 7),  // 7 days
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'LAX'
    ]);

    return $jwt;
}

?>