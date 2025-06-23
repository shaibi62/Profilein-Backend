<?php
include 'origins.php';
include 'dbConnect.php';

header("Content-Type: application/json");

// Initialize response array
$response = ['success' => false, 'error' => ''];

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Turn autocommit off - MySQLi transaction start
    $conn->autocommit(false);

    // Get and validate form data
    $userId = isset($_POST['userId']) ? trim($_POST['userId']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $profession = isset($_POST['profession']) ? trim($_POST['profession']) : '';
    $tagline = isset($_POST['tagline']) ? trim($_POST['tagline']) : '';
    $aboutMe = isset($_POST['aboutMe']) ? trim($_POST['aboutMe']) : '';
    $x = isset($_POST['xLink']) ? trim($_POST['xLink']) : '';
    $fb = isset($_POST['fbLink']) ? trim($_POST['fbLink']) : '';
    $insta = isset($_POST['instaLink']) ? trim($_POST['instaLink']) : '';
    $github = isset($_POST['githubLink']) ? trim($_POST['githubLink']) : '';
    $linkedin = isset($_POST['linkedinLink']) ? trim($_POST['linkedinLink']) : '';

    // Decode JSON arrays with proper initialization
    $education = isset($_POST['education']) ? json_decode($_POST['education'], true) : [];
    $certifications = isset($_POST['certifications']) ? json_decode($_POST['certifications'], true) : [];
    $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];
    $jobs = isset($_POST['jobs']) ? json_decode($_POST['jobs'], true) : [];
    $services = isset($_POST['services']) ? json_decode($_POST['services'], true) : [];
    $projects = isset($_POST['projects']) ? json_decode($_POST['projects'], true) : [];

    // Convert to empty array if null
    $education = $education ?: [];
    $certifications = $certifications ?: [];
    $skills = $skills ?: [];
    $jobs = $jobs ?: [];
    $services = $services ?: [];
    $projects = $projects ?: [];

    // Validate required fields
    if (empty($userId) || empty($name) || empty($email)) {
        throw new Exception("User ID, Name, and Email are required fields");
    }

    // Handle file upload
    $profilePicPath = '';
    $fileName = '';
    if (!empty($_FILES['profilePic']['name'])) {
        $uploadDir = __DIR__ . '/uploads/Profile_Pics/'; // Use server path
        $publicURL = 'http://localhost/Profilein-Backend/uploads/Profile_Pics/'; // For saving into DB

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profilePic']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG or GIF files are allowed");
        }

        $fileExt = pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION);
        $fileName = "Profile-" . $userId . '.' . $fileExt;
        $serverFilePath = $uploadDir . $fileName;
        $profilePicPath = $publicURL . $fileName; // This goes into DB

        if (!move_uploaded_file($_FILES['profilePic']['tmp_name'], $serverFilePath)) {
            throw new Exception("Failed to upload profile picture");
        }

    }

    // 1. Insert personal info
    $stmt = $conn->prepare("INSERT INTO tblPersonalinfo 
                          (usrId, Name, Email, Phone, Address, Profession, Tagline, ProfilePic, AboutMe) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("tblPersonalinfo Prepare failed: " . $conn->error);
    }

    // Ensure all variables are properly initialized
    $phone = $phone ?: null;
    $address = $address ?: null;
    $profession = $profession ?: null;
    $tagline = $tagline ?: null;
    $fileName = $fileName ?: null;
    $aboutMe = $aboutMe ?: null;

    // Bind parameters
    $bindResult = $stmt->bind_param(
        "sssssssss",
        $userId,
        $name,
        $email,
        $phone,
        $address,
        $profession,
        $tagline,
        $profilePicPath,
        $aboutMe
    );

    if (!$bindResult) {
        throw new Exception("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }


    // Ensure all variables are properly initialized
    $x = $x ?: null;
    $fb = $fb ?: null;
    $insta = $insta ?: null;
    $github = $github ?: null;
    $linkedin = $linkedin ?: null;
    // 1. Insert social
    if(empty($x) && empty($fb) && empty($insta) && empty($github) && empty($linkedin)) {
    
    $stmt = $conn->prepare("INSERT INTO tblsocials 
                          (usrId, fbLink, instaLink, xLink, githubLink, linkedinLink) 
                          VALUES (?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("tblsocials Prepare failed: " . $conn->error);
    }


    // Bind parameters
    $bindResult = $stmt->bind_param(
        "ssssss",
        $userId,
        $fb,
        $insta,
        $x,
        $github,
        $linkedin
    );

    if (!$bindResult) {
        throw new Exception("Bind failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }



    // 2. Insert education records
    foreach ($education as $edu) {
        $degree = $edu['degree'] ?? '';
        $institution = $edu['institution'] ?? '';
        $grade = $edu['grade'] ?? '';
        $startYear = $edu['startYear'] ?? '';
        $endYear = $edu['endYear'] ?? '';

        if (!empty($degree)) {
            $stmt = $conn->prepare("INSERT INTO tblEducation 
                                  (usrId, Degree_Name, Institution, Grades, Start_Year, Completion_Year) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("tblEducation Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                "ssssss",
                $userId,
                $degree,
                $institution,
                $grade,
                $startYear,
                $endYear
            );
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
    }
    }
    // 3. Insert certification records
    foreach ($certifications as $certf) {
        $title = $certf['title'] ?? '';
        $institution = $certf['institution'] ?? '';
        $issueDate = $certf['issueDate'] ?? '';

        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO tblCertification 
                                  (usrId, Title, Institution, issueDate) 
                                  VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("tblCertification Prepare failed: " . $conn->error);
            }

            $issueDate = !empty($issueDate) ? date('Y-m-d', strtotime($issueDate)) : null;

            $stmt->bind_param(
                "ssss",
                $userId,
                $title,
                $institution,
                $issueDate
            );
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
    }

    // 4. Insert skills records
    foreach ($skills as $skill) {
        $title = $skill['title'] ?? '';
        $experience = $skill['experience'] ?? '';

        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO tblSkill 
                                  (usrId, Title, Experience) 
                                  VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("tblSkills Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                "sss",
                $userId,
                $title,
                $experience
            );
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
    }

    // 3. Insert jobs records
    foreach ($jobs as $job) {
        $title = $job['title'] ?? '';
        $company = $job['company'] ?? '';
        $description = $job['description'] ?? '';
        $startdate = $job['startdate'] ?? '';
        $enddate = $job['enddate'] ?? '';

        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO tbljob 
                                  (usrId, Title, Company,Description, startDate, endDate) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("tblCertification Prepare failed: " . $conn->error);
            }

            $startdate = !empty($startdate) ? date('Y-m-d', strtotime($startdate)) : null;
            $enddate = !empty($enddate) ? date('Y-m-d', strtotime($enddate)) : null;

            $stmt->bind_param(
                "ssssss",
                $userId,
                $title,
                $company,
                $description,
                $startdate,
                $enddate
            );
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
    }

    // 3. Insert services records
    foreach ($services as $serv) {
        $title = $serv['title'] ?? '';
        $description = $serv['description'] ?? '';

        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO tblservice 
                                  (usrId, Title, Description) 
                                  VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("tblservice Prepare failed: " . $conn->error);
            }


            $stmt->bind_param(
                "sss",
                $userId,
                $title,
                $description
            );
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
    }


    // 5. Insert projects records
    foreach ($projects as $project) {
        $title = $project['title'] ?? '';
        $description = $project['description'] ?? '';
        $link = $project['link'] ?? '';

        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO tblProject 
                                  (usrId, Title, Description, Link) 
                                  VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("tblProjects Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                "ssss",
                $userId,
                $title,
                $description,
                $link
            );
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }
    }

    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'User information saved successfully';

} catch (Exception $e) {
    $conn->rollback();

    // Delete uploaded file if transaction failed
    if (!empty($profilePicPath) && file_exists($profilePicPath)) {
        unlink($profilePicPath);
    }

    http_response_code(500);
    $response['error'] = $e->getMessage();
} finally {
    $conn->autocommit(true);
    echo json_encode($response);
}
?>