<?php

    include 'origins.php';
    include 'dbConnect.php';
    include 'me.php';

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'POST':
            try{

            }
            catch(Exception $e){
                echo json_encode([
                    "success" => false,
                    "message" => "Unexpected error: " . $e->getMessage(),
                    "code" => "SERVER_ERROR"
                ]);
            }
    }
?>