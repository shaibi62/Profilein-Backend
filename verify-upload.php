<?php
    header('Content-Type: application/json');
    include 'origins.php';

    // Must match the path in addtemplate.php exactly
    $frontendPublicPath = 'C:/Users/dell/Desktop/ProfileIn/public/';

    try {
        if (!isset($_GET['template']) || !isset($_GET['image'])) {
            throw new Exception("Missing parameters");
        }

        // Sanitize input paths
        $templatePath = $frontendPublicPath . str_replace(['../', '..\\'], '', ltrim($_GET['template'], '/\\'));
        $imagePath = $frontendPublicPath . str_replace(['../', '..\\'], '', ltrim($_GET['image'], '/\\'));

        // Convert to proper Windows paths
        $templatePath = str_replace('/', DIRECTORY_SEPARATOR, $templatePath);
        $imagePath = str_replace('/', DIRECTORY_SEPARATOR, $imagePath);

        // Security check - prevent directory traversal
        if (strpos(realpath($templatePath), realpath($frontendPublicPath)) !== 0 || 
            strpos(realpath($imagePath), realpath($frontendPublicPath)) !== 0) {
            throw new Exception("Invalid path detected - potential directory traversal attempt");
        }

        echo json_encode([
            "exists" => file_exists($templatePath) && file_exists($imagePath),
            "template_exists" => file_exists($templatePath),
            "image_exists" => file_exists($imagePath),
            "paths" => [
                "template" => str_replace($frontendPublicPath, '', $templatePath),
                "image" => str_replace($frontendPublicPath, '', $imagePath)
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            "error" => $e->getMessage(),
            "debug" => [
                "frontend_path" => $frontendPublicPath,
                "received_template" => $_GET['template'] ?? null,
                "received_image" => $_GET['image'] ?? null
            ]
        ]);
    }
?>