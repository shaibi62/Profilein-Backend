<?php
    $server = 'localhost';
    $dbname = 'profilein';
    $user = 'root';
    $password = '';

    $conn = new mysqli($server, $user, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
