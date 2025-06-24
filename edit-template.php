<?php
include 'origins.php';
header('Content-Type: application/json');
include 'dbConnect.php'; // Ensure DB connection is included

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$baseURL = 'http://localhost/Profilein-Backend/uploads';
$frontendPublicPath = __DIR__ . '/../Profilein-Backend/uploads/';
$templatesBasePath = $frontendPublicPath . 'templates/';
$imagesBasePath = $frontendPublicPath . 'images/';

function sanitizeFolderName($string) {
    $string = trim($string);
    $string = preg_replace('/[^a-zA-Z0-9_-]/', '_', $string);
    $string = preg_replace('/_+/', '_', $string);
    return strlen($string) === 0 ? 'template' : trim($string, '_');
}

function deleteFolder($folderPath) {
    if (!is_dir($folderPath)) return;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file) : unlink($file);
    }
    rmdir($folderPath);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $tempId = $_POST['id'] ?? null;
    if (!$tempId) throw new Exception("Template ID is required");
   
    // Fetch current data
    $query = "SELECT * FROM tbltemplate WHERE tmpId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tempId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) throw new Exception("Template not found");
    $currentData = $result->fetch_assoc();
    $stmt->close();
    $FolderId = $currentData['FolderId'];
    $oldTitle = $currentData['Title'];
    $oldImage = $currentData['Image'];
    $oldTemplateURL = $currentData['Template_Address'];

    $oldFolderName = "Template-". $FolderId;
    $oldFolderPath = $templatesBasePath . $oldFolderName . '/';

    $title = $_POST['title'] ?? null;
    $category = $_POST['category'] ?? null;
    $feature1 = $_POST['feature1'] ?? null;
    $feature2 = $_POST['feature2'] ?? null;
    $feature3 = $_POST['feature3'] ?? null;

    $fields = [];
    $params = [];
    $types = "";

    if ($title !== null) {
        $fields[] = "Title = ?";
        $params[] = $title;
        $types .= "s";
    }

    if ($category !== null) {
        $fields[] = "Category = ?";
        $params[] = $category;
        $types .= "s";
    }

    if ($feature1 !== null) {
        $fields[] = "Feature1 = ?";
        $params[] = $feature1;
        $types .= "s";
    }

    if ($feature2 !== null) {
        $fields[] = "Feature2 = ?";
        $params[] = $feature2;
        $types .= "s";
    }

    if ($feature3 !== null) {
        $fields[] = "Feature3 = ?";
        $params[] = $feature3;
        $types .= "s";
    }

    // Handle image upload
    if (isset($_FILES['previewImage']) && $_FILES['previewImage']['error'] === UPLOAD_ERR_OK) {
        // Delete old image
        if (!empty($oldImage)) {
            $oldImagePath = $frontendPublicPath . str_replace($baseURL . '/', '', $oldImage);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $imgExt = pathinfo($_FILES['previewImage']['name'], PATHINFO_EXTENSION);
        $imgName = 'img_' . uniqid() . '.' . $imgExt;
        $imgPath = $imagesBasePath . $imgName;

        if (!move_uploaded_file($_FILES['previewImage']['tmp_name'], $imgPath)) {
            throw new Exception("Failed to save preview image");
        }

        $imageRelURL = "$baseURL/images/$imgName";
        $fields[] = "Image = ?";
        $params[] = $imageRelURL;
        $types .= "s";
    }

    // Handle zip upload
    if (isset($_FILES['templateZip']) && $_FILES['templateZip']['error'] === UPLOAD_ERR_OK && $title !== null) {
        // Delete old folder
        deleteFolder($oldFolderPath);

        $templateFolderName = "Template-". $FolderId;
        $templateDir = $templatesBasePath . $templateFolderName . '/';

        if (!file_exists($templatesBasePath)) mkdir($templatesBasePath, 0755, true);
        if (!file_exists($imagesBasePath)) mkdir($imagesBasePath, 0755, true);
        if (!is_dir($templateDir) && !mkdir($templateDir, 0755, true)) {
            throw new Exception("Failed to create template directory");
        }

        $tempExtractDir = $templatesBasePath . 'temp_' . uniqid() . '/';
        if (!mkdir($tempExtractDir, 0755, true)) {
            throw new Exception("Failed to create temp extract directory");
        }

        $tmpZip = $_FILES['templateZip']['tmp_name'];
        if (!file_exists($tmpZip)) {
            throw new Exception("ZIP file not uploaded properly");
        }

        $zip = new ZipArchive;
        if ($zip->open($tmpZip) !== TRUE) {
            throw new Exception("Failed to open ZIP archive");
        }
        $zip->extractTo($tempExtractDir);
        $zip->close();

        $extractedItems = array_diff(scandir($tempExtractDir), ['.', '..']);
        $singleRoot = count($extractedItems) === 1 && is_dir($tempExtractDir . $extractedItems[array_key_first($extractedItems)]);
        $rootDir = $singleRoot ? $tempExtractDir . $extractedItems[array_key_first($extractedItems)] . '/' : $tempExtractDir;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relPath = substr($file->getPathname(), strlen($rootDir));
            $destPath = $templateDir . $relPath;
            if ($file->isDir()) {
                if (!file_exists($destPath)) mkdir($destPath, 0755, true);
            } else {
                rename($file->getPathname(), $destPath);
            }
        }

        deleteFolder($tempExtractDir);

        $templateRelURL = "$baseURL/templates/$templateFolderName/";
        $fields[] = "Template_Address = ?";
        $params[] = $templateRelURL;
        $types .= "s";
    }


    if (count($fields) === 0) {
        throw new Exception("No update fields provided.");
    }

    $query = "UPDATE tbltemplate SET " . implode(', ', $fields) . " WHERE tmpId = ?";
    $params[] = $tempId;
    $types .= "i";

    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

    echo json_encode([
        "success" => true,
        "message" => "Template updated successfully"
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
