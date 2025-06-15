<?php

include 'origins.php';
header("Content-Type: application/json");

function injectData($userId, $tempId) {
    include 'dbConnect.php';

    // Fetch Template Path
    $stmt = $conn->prepare("SELECT Template_Address FROM tbltemplate WHERE tmpId = ?");
    if (!$stmt) return ['success' => false, 'error' => 'Database query failed: ' . $conn->error];

    $stmt->bind_param("s", $tempId);
    if (!$stmt->execute()) return ['success' => false, 'error' => 'Execution failed: ' . $stmt->error];

    $result = $stmt->get_result();
    if ($result->num_rows === 0) return ['success' => false, 'error' => 'Template not found'];

    $BaseURL = $result->fetch_assoc()['Template_Address'];
    $stmt->close();

    $templatePath = $BaseURL . "index.html";
    $outputDir = $_SERVER['DOCUMENT_ROOT'] . "/frontend/public/uploads/output/";

    // Fetch dynamic data
    $personal = fetchData($conn, "SELECT * FROM tblPersonalInfo WHERE usrId = ?", $userId)[0] ?? [];
    $education = fetchData($conn, "SELECT * FROM tbleducation WHERE usrId = ?", $userId);
    $skills = fetchData($conn, "SELECT * FROM tblskill WHERE usrId = ?", $userId);
    $projects = fetchData($conn, "SELECT * FROM tblproject WHERE usrId = ?", $userId);
    $certifications = fetchData($conn, "SELECT * FROM tblcertification WHERE usrId = ?", $userId);

    // Safely extract first education/project item
    $edu = $education[0] ?? [];
    $proj = $projects[0] ?? [];

    // Flatten skills into comma-separated string
    $skillNames = implode(', ', array_column($skills, 'Title'));

    // Prepare placeholders
    $data = [
        '{{Baseurl}}' => $BaseURL,
        '{{name}}' => $personal['Name'] ?? '',
        '{{email}}' => $personal['Email'] ?? '',
        '{{phone}}' => $personal['Phone'] ?? '',
        '{{address}}' => $personal['Address'] ?? '',
        '{{about}}' => $personal['Tagline'] ?? '',
        '{{typed_items}}' => $skillNames,
        '{{skill}}' => $skillNames,
        '{{experience}}' => $skills['Experience'] ?? '90%',
        '{{tagline}}' => $personal['Tagline'] ?? '',
        '{{degree_name}}' => $edu['Degree_Name'] ?? '',
        '{{start_year}}' => $edu['Start_Year'] ?? '',
        '{{end_year}}' => $edu['Completion_Year'] ?? '',
        '{{institute}}' => $edu['Institution'] ?? '',
        '{{grades}}' => $edu['Grades'] ?? '',
        '{{job_name}}' => "jobTitle" ,
        '{{start}}' => "jobStart" ,
        '{{end}}' => "jobEnd" ,
        '{{company}}' => "company" ,
        '{{job_description}}' => "jobDesc" ,
        '{{service_name}}' => "serviceName" ,
        '{{service_description}}' => "serviceDesc" ,
        '{{clients_no}}' => "10 ",
        '{{projects_no}}' => "10 ",
        '{{support_hours}}' => "100 ",
        '{{project_name}}' => $proj['Title'] ,
        '{{project_description}}' => $proj['Description'] ,
        '{{project_link}}' => $proj['Link'] ,
        '{{x_link}}' => "x.com" ?? '',
        '{{fb_link}}' => "facebook.com" ,
        '{{insta_link}}' => "instagram.com" ,
        '{{linkedin_link}}' => "linkedin.com" 
    ];

    // Read template
    $templateContent = file_get_contents($templatePath);
    if ($templateContent === false) {
        return ['success' => false, 'error' => 'Failed to read template file.'];
    }

    // Replace placeholders with actual data
    $finalHtml = strtr($templateContent, $data);

    // Save the file
    $fileName = "portfolio-user{$userId}-temp{$tempId}.html";
    $outputPath = $outputDir . $fileName;

    if (file_put_contents($outputPath, $finalHtml) === false) {
        return ['success' => false, 'error' => 'Failed to save generated file.'];
    }

    return [
        'success' => true,
        'output_file' => "http://localhost/frontend/public/uploads/output/$fileName"
    ];
}


function fetchData($conn, $query, $userId) {
    $stmt = $conn->prepare($query);
    if (!$stmt) return null;
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>
