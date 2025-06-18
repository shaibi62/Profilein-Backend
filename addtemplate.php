<?php
header('Content-Type: application/json');
include 'origins.php';
include 'dbConnect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');
ini_set('max_execution_time', '300');

// Update this if frontend public folder is moved
$frontendPublicPath = 'C:/xampp/htdocs/Profilein-Backend/uploads/';
$templatesBasePath = $frontendPublicPath . 'templates/';
$imagesBasePath = $frontendPublicPath . 'images/';
$baseURL = 'http://localhost/Profilein-Backend/uploads';

function sanitizeFolderName($string) {
    $string = trim($string);
    $string = preg_replace('/[^a-zA-Z0-9_-]/', '_', $string);
    $string = preg_replace('/_+/', '_', $string);
    return strlen($string) === 0 ? 'template' : trim($string, '_');
}

try {
    if (
        !isset($_POST['title'], $_POST['category'], $_POST['feature1'], $_POST['feature2'], $_POST['feature3']) ||
        !isset($_FILES['previewImage']) || !isset($_FILES['templateZip'])
    ) {
        throw new Exception("Missing required fields");
    }

    $title = $_POST['title'];
    $category = $_POST['category'];
    $feature1 = $_POST['feature1'];
    $feature2 = $_POST['feature2'];
    $feature3 = $_POST['feature3'];

    $templateFolderName = sanitizeFolderName($title);
    $templateDir = $templatesBasePath . $templateFolderName . '/';

    if (file_exists($templateDir)) {
        throw new Exception("Template folder '$templateFolderName' already exists.");
    }

    if (!file_exists($templatesBasePath)) mkdir($templatesBasePath, 0755, true);
    if (!file_exists($imagesBasePath)) mkdir($imagesBasePath, 0755, true);

    // Handle image
$previewImage = $_FILES['previewImage'];
if ($previewImage['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("Error uploading preview image");
}

$imageExt = pathinfo($previewImage['name'], PATHINFO_EXTENSION);
$imageFilename = 'img_' . uniqid() . '.' . $imageExt;
$imagePath = $imagesBasePath . $imageFilename;

if (!move_uploaded_file($previewImage['tmp_name'], $imagePath)) {
    throw new Exception("Failed to save preview image");
}

// Handle ZIP upload
    $templateZip = $_FILES['templateZip'];
    if ($templateZip['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error uploading template ZIP file");
    }

    // Create template directory
    if (!mkdir($templateDir, 0755, true)) {
        throw new Exception("Failed to create template directory");
    }

    // Extract ZIP to temporary folder
    $tempExtractDir = $templatesBasePath . 'temp_' . uniqid() . '/';
    mkdir($tempExtractDir, 0755, true);

    $zip = new ZipArchive;
    if ($zip->open($templateZip['tmp_name']) !== TRUE) {
        throw new Exception("Failed to open ZIP archive");
    }

    if (!$zip->extractTo($tempExtractDir)) {
        $zip->close();
        throw new Exception("Failed to extract ZIP archive");
    }
    $zip->close();

    // Check for single root folder in ZIP
    $extractedItems = array_diff(scandir($tempExtractDir), ['.', '..']);
    if (count($extractedItems) === 1 && is_dir($tempExtractDir . $extractedItems[array_key_first($extractedItems)])) {
        $singleRootFolder = $tempExtractDir . $extractedItems[array_key_first($extractedItems)] . '/';

        // Move contents of this root folder to templateDir
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($singleRootFolder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relativePath = substr($file->getPathname(), strlen($singleRootFolder));
            $targetPath = $templateDir . $relativePath;

            if ($file->isDir()) {
                if (!file_exists($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                rename($file->getPathname(), $targetPath);
            }
        }
    } else {
        // Otherwise, move everything from tempExtractDir to templateDir
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempExtractDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relativePath = substr($file->getPathname(), strlen($tempExtractDir));
            $targetPath = $templateDir . $relativePath;

            if ($file->isDir()) {
                if (!file_exists($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                rename($file->getPathname(), $targetPath);
            }
        }
    }

    // Cleanup temp directory
    $it = new RecursiveDirectoryIterator($tempExtractDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($tempExtractDir);

    
    // === Save to Database ===
    $stmt = $conn->prepare("INSERT INTO tbltemplate (Title, Category, Feature1, Feature2, Feature3, Image, Template_Address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("DB prepare failed: " . $conn->error);

    $imageRelURL = "$baseURL/images/$imageFilename";
    $templateRelURL = "$baseURL/templates/$templateFolderName/";

    $stmt->bind_param("sssssss", $title, $category, $feature1, $feature2, $feature3, $imageRelURL, $templateRelURL);
    if (!$stmt->execute()) throw new Exception("DB execute failed: " . $stmt->error);

    $stmt->close();
    $conn->close();

    echo json_encode([
        "success" => true,
        "message" => "Template uploaded successfully.",
        "template_id" => $templateFolderName,
        "template_url" => $templateRelURL,
        "preview_image" => $imageRelURL
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
