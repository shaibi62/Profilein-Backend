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
    $outputDir = $_SERVER['DOCUMENT_ROOT'] . "/Profilein-Backend/uploads/output/";

    // Fetch dynamic data
    $personal = fetchData($conn, "SELECT * FROM tblPersonalInfo WHERE usrId = ?", $userId)[0] ?? [];
    $education = fetchData($conn, "SELECT * FROM tbleducation WHERE usrId = ?", $userId);
    $skills = fetchData($conn, "SELECT * FROM tblskill WHERE usrId = ?", $userId);
    $services = fetchData($conn, "SELECT * FROM tblservice WHERE usrId = ?", $userId);
    $jobs = fetchData($conn, "SELECT * FROM tbljob WHERE usrId = ?", $userId);
    $projects = fetchData($conn, "SELECT * FROM tblproject WHERE usrId = ?", $userId);
    $certifications = fetchData($conn, "SELECT * FROM tblcertification WHERE usrId = ?", $userId);

    // Safely extract first education/project item
  
    $certifications = $certifications[0] ?? [];
    // Flatten skills into comma-separated string
    $skillNames = implode(', ', array_column($skills, 'Title'));
    $Allskills = "";
foreach ($skills as $skill) {
    if (isset($skill['Title'])) {
        // Set progress based on experience level
        $level = strtolower(trim($skill['Experience']));
        $progress = 0;

        switch ($level) {
            case 'beginner':
                $progress = 30;
                break;
            case 'intermediate':
                $progress = 60;
                break;
            case 'expert':
                $progress = 100;
                break;
            default:
                $progress = 0; // fallback for unknown levels
        }

        $Allskills .= ' 
        <div class="progress">
            <span class="skill">
                <span>' . htmlspecialchars($skill['Title']) . '</span>
                <i class="val">' . htmlspecialchars($skill['Experience']) . '</i>
            </span>
            <div class="progress-bar-wrap">
                <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="' . $progress . '"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: ' . $progress . '%"
                ></div>
            </div>
        </div>
        ';
    }
}

$resumeItems ="";
foreach($jobs as $job) {
    $resumeItems .= '<div class="resume-item">
                <h4>'.$job['Title'].'</h4>
                <h5>['.$job['startDate'].'] / ['.$job['endDate'].']</h5>
                <p><em>'.$job['Company'].' </em></p>
                <ul>
                  <li>'.$job['Description'].'</li>
                </ul>
              </div>';
}


$Education = "";
foreach ($education as $edu) {
    $Education .= '
  
    <div class="resume-item">
                <h4>'.$edu['Degree_Name'].'</h4>
                <h5>['.$edu['Start_Year'].'] / ['.$edu['Completion_Year'].']</h5>
                <p><em>'.$edu['Institution'].' </em></p>
                <ul>
                  <li>'.$edu['Grades'].'</li>
                </ul>
              </div>';
}

$service_item = "";
foreach ($services as $service) {
    $service_item .= '
      <div class="service-item col-lg-3 col-md-6" style="margin:50px">
                <div class="icon">
                  <i class="bi bi-briefcase"></i>
                </div>
                <a href="#" class="stretched-link">
                  <h3>'.$service['Title'].'</h3>
                </a>
                <p>'.$service['Description'].'</p>
              </div>';
}

$project_item = "";
foreach($projects as $proj)
{
    $project_item .= '
    <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-product"
              >
                <img
                  src="'. $BaseURL.'assets/img/portfolio/app-1.jpg"
                  class="img-fluid"
                  alt=""
                />
                <div class="portfolio-info">
                  <h4>'.$proj['Title'].'</h4>
                  <p>'.$proj['Description'].'</p>
                  
                  <a
                    href="'.$proj['Link'].'"
                    title="More Details"
                    class="details-link"
                    ><i class="bi bi-link-45deg"></i
                  ></a>
                </div>
              </div>
    ';
}





    // Prepare placeholders
    $data = [
        '{{Baseurl}}' => $BaseURL,
        '{{name}}' => $personal['Name'] ?? '',
        '{{email}}' => $personal['Email'] ?? '',
        '{{phone}}' => $personal['Phone'] ?? '',
        '{{address}}' => $personal['Address'] ?? '',
        '{{about}}' => $personal['Tagline'] ?? '',
        '{{Profile-Pic}}' => $personal['ProfilePic'] ?? '',
        '{{typed_items}}' => $skillNames,
        '{{skills}}' => $Allskills,
        '{{experience}}' => $skills['Experience'] ?? '90%',
        '{{tagline}}' => $personal['Tagline'] ?? '',
        '{{education_items}}' => $Education ?? '',
        '{{resume_items}}' => $resumeItems ?? '',
        '{{service_items}}' => $service_item ?? '',
        '{{clients_no}}' => "10 ",
        '{{projects_no}}' => "10 ",
        '{{support_hours}}' => "100 ",
        '{{project_items}}' => $project_item ,
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
        'output_file' => "http://localhost/Profilein-Backend/uploads/output/$fileName"
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
