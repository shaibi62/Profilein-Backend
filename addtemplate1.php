<?php
header('Content-Type: application/json');
include 'origins.php';
include 'dbConnect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set upload limits (adjust if needed)
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');
ini_set('max_execution_time', '300');

$frontendPublicPath = 'C:/Users/dell/Desktop/ProfileIn/public/';
$templatesBasePath = $frontendPublicPath . 'Templates/';
$uploadsBasePath = $frontendPublicPath . 'uploads/';

// Helper to sanitize folder names

function sanitizeFolderName($string) {
    $string = trim($string); // remove leading/trailing spaces

    // Replace spaces and invalid characters with underscore
    $string = preg_replace('/[^a-zA-Z0-9_-]/', '_', $string);

    // Remove multiple underscores in a row
    $string = preg_replace('/_+/', '_', $string);

    // Remove underscores at start or end
    $string = trim($string, '_');

    // If string is empty after cleanup, fallback to 'template'
    if (strlen($string) === 0) {
        $string = 'template';
    }

    return $string;
}

try {
    // Validate POST data
    if (
        !isset($_POST['title'], $_POST['category'], $_POST['feature1'], $_POST['feature2'], $_POST['feature3']) ||
        !isset($_FILES['previewImage']) ||
        !isset($_FILES['templateZip'])
    ) {
        throw new Exception("Missing required fields");
    }

    $title = $_POST['title'];
    $category = $_POST['category'];
    $feature1 = $_POST['feature1'];
    $feature2 = $_POST['feature2'];
    $feature3 = $_POST['feature3'];

    // Sanitize and prepare template folder name
    $templateFolderName = sanitizeFolderName($title);
    $templateDir = $templatesBasePath . $templateFolderName . '/';

    // Check if template folder already exists
    if (file_exists($templateDir)) {
        throw new Exception("Template folder '$templateFolderName' already exists. Use a different title.");
    }

    // Create directories if not exist
    if (!file_exists($templatesBasePath)) {
        mkdir($templatesBasePath, 0755, true);
    }
    if (!file_exists($uploadsBasePath)) {
        mkdir($uploadsBasePath, 0755, true);
    }

    // Handle preview image upload
    $previewImage = $_FILES['previewImage'];
    if ($previewImage['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error uploading preview image");
    }

    $imageExt = pathinfo($previewImage['name'], PATHINFO_EXTENSION);
    $imageFilename = 'img_' . uniqid() . '.' . $imageExt;
    $imagePath = $uploadsBasePath . $imageFilename;

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

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO tbltemplate (Title, Category, Feature1, Feature2, Feature3, Image) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("DB prepare failed: " . $conn->error);
    }

    // Save image path relative to public folder (e.g. "uploads/filename.jpg")
    $imageRelPath = 'uploads/' . $imageFilename;

    $stmt->bind_param("ssssss", $title, $category, $feature1, $feature2, $feature3, $imageRelPath);
    if (!$stmt->execute()) {
        throw new Exception("DB execute failed: " . $stmt->error);
    }

    $insertedTemplateID = $stmt->insert_id;

    $stmt->close();
    $conn->close();

    // Send success response
    echo json_encode([
        "success" => true,
        "message" => "Template uploaded and saved successfully",
        "template_id" => $templateFolderName,
        "template_dir" => 'Templates/' . $templateFolderName . '/',
        "image" => $imageRelPath
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
    ]);
}
?>
