<?php
if (!isset($_GET['file'])) {
    http_response_code(400);
    exit("Missing file.");
}

$filename = basename($_GET['file']); // Sanitize input
$filePath = __DIR__ . "/../Profilein-Backend/uploads/output/" . $filename;

if (!file_exists($filePath)) {
    http_response_code(404);
    exit("File not found.");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
