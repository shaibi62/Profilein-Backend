<?php

include 'origins.php';
header(header: "Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Read raw JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (!isset($input['tempId']) || empty($input['tempId'])) {
    echo json_encode(['success' => false, 'error' => 'Missing or empty tempId']);
    exit;
}
$tempId = trim($input['tempId']);
$genResult = injectData($tempId);
if (!$genResult['success']) {
    echo json_encode(['success' => false, 'error' => $genResult['error']]);
    exit;
}

$path = $genResult['output_file'];

echo json_encode(['success' => true, 'message' => 'Portfolio saved successfully', 'path' => $path]);


function injectData( $tempId) {
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
    $outputDir = $_SERVER['DOCUMENT_ROOT'] . "/Profilein-Backend/uploads/preview/";

    // Fetch dynamic data
    $personal =  [];
    $skills = [];
    $certifications = [];

    // Safely extract first education/project item
  
    $certifications = $certifications[0] ?? [];
    // Flatten skills into comma-separated string


        $Allskills = ' 
        <div class="progress">
            <span class="skill">
                <span>Skill1</span>
                <i class="val">expert</i>
            </span>
            <div class="progress-bar-wrap">
                <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="100"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 100%"
                ></div>
            </div>
        </div>
        <div class="progress">
            <span class="skill">
                <span>Skill2</span>
                <i class="val">intermediate</i>
            </span>
            <div class="progress-bar-wrap">
                <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="70"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 70%"
                ></div>
            </div>
        </div>
        <div class="progress">
            <span class="skill">
                <span>Skill3</span>
                <i class="val">basic</i>
            </span>
            <div class="progress-bar-wrap">
                <div
                    class="progress-bar"
                    role="progressbar"
                    aria-valuenow="50"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: 50%"
                ></div>
            </div>
        </div>
        ';
    



    $resumeItems = '<div class="resume-item">
                <h4>Job title</h4>
                <h5>[startDate] / [endDate]</h5>
                <p><em>Company </em></p>
                <ul>
                  <li>Job Description</li>
                </ul>
              </div>
              <div class="resume-item">
                <h4>Job title</h4>
                <h5>[startDate] / [endDate]</h5>
                <p><em>Company </em></p>
                <ul>
                  <li>Job Description</li>
                </ul>
              </div>
              <div class="resume-item">
                <h4>Job title</h4>
                <h5>[startDate] / [endDate]</h5>
                <p><em>Company </em></p>
                <ul>
                  <li>Job Description</li>
                </ul>
              </div>';




    $Education = '
  
    <div class="resume-item">
                <h4>BS </h4>
                <h5>2021 / [2025]</h5>
                <p><em>ProfileIn</em></p>
                <ul>
                  <li>A+</li>
                </ul>
              </div>
                <div class="resume-item">
                <h4>BS </h4>
                <h5>2021 / [2025]</h5>
                <p><em>ProfileIn</em></p>
                <ul>
                  <li>A+</li>
                </ul>
              </div>
                <div class="resume-item">
                <h4>BS </h4>
                <h5>2021 / [2025]</h5>
                <p><em>ProfileIn</em></p>
                <ul>
                  <li>A+</li>
                </ul>
              </div>';


    $service_item = '
      <div class="service-item col-lg-3 col-md-6" style="margin:50px">
                <div class="icon">
                  <i class="bi bi-briefcase"></i>
                </div>
                <a href="#" class="stretched-link">
                  <h3>Web Development</h3>
                </a>
                <p>Building responsive and functional websites.</p>
              </div>
              <div class="service-item col-lg-3 col-md-6" style="margin:50px">
                <div class="icon">
                  <i class="bi bi-briefcase"></i>
                </div>
                <a href="#" class="stretched-link">
                  <h3>Web Development</h3>
                </a>
                <p>Building responsive and functional websites.</p>
              </div>
              <div class="service-item col-lg-3 col-md-6" style="margin:50px">
                <div class="icon">
                  <i class="bi bi-briefcase"></i>
                </div>
                <a href="#" class="stretched-link">
                  <h3>Web Development</h3>
                </a>
                <p>Building responsive and functional websites.</p>
              </div>';



    $project_item = '
    <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-product"
              >
                <img
                  src="'. $BaseURL.'assets/img/portfolio/app-1.jpg"
                  class="img-fluid"
                  alt=""
                />
                <div class="portfolio-info">
                  <h4>Project Title</h4>
                  <p>Project Description</p>

                  <a
                    href="#"
                    title="More Details"
                    class="details-link"
                    ><i class="bi bi-link-45deg"></i
                  ></a>
                </div>
              </div>
              <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-product"
              >
                <img
                  src="'. $BaseURL.'assets/img/portfolio/app-1.jpg"
                  class="img-fluid"
                  alt=""
                />
                <div class="portfolio-info">
                  <h4>Project Title</h4>
                  <p>Project Description</p>

                  <a
                    href="#"
                    title="More Details"
                    class="details-link"
                    ><i class="bi bi-link-45deg"></i
                  ></a>
                </div>
              </div>
              <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-product"
              >
                <img
                  src="'. $BaseURL.'assets/img/portfolio/app-1.jpg"
                  class="img-fluid"
                  alt=""
                />
                <div class="portfolio-info">
                  <h4>Project Title</h4>
                  <p>Project Description</p>

                  <a
                    href="#"
                    title="More Details"
                    class="details-link"
                    ><i class="bi bi-link-45deg"></i
                  ></a>
                </div>
              </div>
    ';






    // Prepare placeholders
    $data = [
        '{{Baseurl}}' => $BaseURL,
        '{{name}}' =>  'ProfileIn User',
        '{{email}}' =>  'user@example.com',
        '{{phone}}' =>  '123-456-7890',
        '{{profession}}' =>  'Web Developer',
        '{{address}}' =>  '123 Main St, Anytown, USA',
        '{{about}}' =>  'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        '{{Profile-Pic}}' => $BaseURL . 'assets/img/profile-img.jpg', // Example profile picture, replace with actual
        '{{typed_items}}' => "skill1, skill2, skill3", // Example skills, replace with actual
        '{{skills}}' => $Allskills,
        '{{experience}}' => $skills['Experience'] ?? '90%',
        '{{tagline}}' => $personal['Tagline'] ?? '',
        '{{education_items}}' => $Education ?? '',
        '{{resume_items}}' => $resumeItems ?? '',
        '{{service_items}}' => $service_item ?? '',
        '{{project_items}}' => $project_item ,
        '{{x_link}}' =>  '',
        '{{fb_link}}' =>  '' ,
        '{{insta_link}}' =>  '' ,
        '{{linkedin_link}}' =>  '', 
        '{{github_link}}' => '', 
    ];

    // Read template
    $templateContent = file_get_contents($templatePath);
    if ($templateContent === false) {
        return ['success' => false, 'error' => 'Failed to read template file.'];
    }

    // Replace placeholders with actual data
    $finalHtml = strtr($templateContent, $data);

    // Save the file
    $fileName = "preview-temp{$tempId}.html";
    $outputPath = $outputDir . $fileName;

    if (file_put_contents($outputPath, $finalHtml) === false) {
        return ['success' => false, 'error' => 'Failed to save generated file.'];
    }

    return [
        'success' => true,
        'output_file' => "http://localhost/Profilein-Backend/uploads/preview/$fileName"
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
