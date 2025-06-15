<?php
$userId = $_GET['id'] ?? null;
if (!$userId) die("User ID is required.");

// Fetch JSON data from API
$Baseurl = "http://localhost/Profilein-Backend/";
$apiUrl = $Baseurl."getUserData.php?id=" . urlencode($userId);
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!$data['success']) die("Error: " . $data['error']);

// Extract values
$personal = $data['data']['personalInfo'];
$education = $data['data']['education'][0] ?? [];
$skills = $data['data']['skills'];
$projects = $data['data']['projects'];
$certs = $data['data']['certifications'];

// Load template
$templatePath = __DIR__ . '/template/index.html';
$template = file_get_contents($templatePath);

// Prepare replacements
$replacements = [
    '{{name}}' => $personal['Name'] ?? '',
    '{{email}}' => $personal['Email'] ?? '',
    '{{phone}}' => $personal['Phone'] ?? '',
    '{{address}}' => $personal['Address'] ?? '',
    '{{tagline}}' => $personal['Tagline'] ?? '',
    '{{about}}' => $personal['Profession'] ?? '',
    '{{typed_items}}' => 'Developer, Designer, Freelancer',
    '{{degree_name}}' => $education['Degree'] ?? '',
    '{{start_year}}' => $education['StartYear'] ?? '',
    '{{end_year}}' => $education['EndYear'] ?? '',
    '{{institute}}' => $education['Institute'] ?? '',
    '{{grades}}' => $education['Grades'] ?? '',
    '{{job_name}}' => $certs[0]['Title'] ?? '',
    '{{company}}' => $certs[0]['Issuer'] ?? '',
    '{{start}}' => $certs[0]['StartDate'] ?? '',
    '{{end}}' => $certs[0]['EndDate'] ?? '',
    '{{job_description}}' => $certs[0]['Description'] ?? '',
    '{{skill}}' => $skills[0]['Name'] ?? '',
    '{{experience}}' => $skills[0]['Level'] ?? '',
    '{{service_name}}' => $skills[1]['Name'] ?? '',
    '{{service_description}}' => $skills[1]['Level'] ?? '',
    '{{project_name}}' => $projects[0]['Title'] ?? '',
    '{{project_description}}' => $projects[0]['Description'] ?? '',
    '{{project_link}}' => $projects[0]['Link'] ?? '',
    '{{clients_no}}' => count($certs),
    '{{projects_no}}' => count($projects),
    '{{support_hours}}' => 500,
    '{{x_link}}' => '#',
    '{{fb_link}}' => '#',
    '{{insta_link}}' => '#',
    '{{linkedin_link}}' => '#'
];

// Replace placeholders
foreach ($replacements as $key => $value) {
    $template = str_replace($key, htmlspecialchars($value, ENT_QUOTES), $template);
}

// Save HTML file
$outputDir = __DIR__ . '/output/' . preg_replace('/[^a-z0-9]/i', '_', strtolower($personal['Name'] ?? 'user'));
if (!file_exists($outputDir)) mkdir($outputDir, 0755, true);
file_put_contents($outputDir . '/index.html', $template);

// Copy assets
function copyAssets($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if ($file !== '.' && $file !== '..') {
            $from = "$src/$file";
            $to = "$dst/$file";
            if (is_dir($from)) {
                copyAssets($from, $to);
            } else {
                copy($from, $to);
            }
        }
    }
    closedir($dir);
}
copyAssets(__DIR__ . '/template/assets', $outputDir . '/assets');

echo "✅ Portfolio generated: <a href='output/" . basename($outputDir) . "/index.html' target='_blank'>View</a>";
