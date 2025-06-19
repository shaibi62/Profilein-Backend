<?php
include 'origins.php';
include 'dbConnect.php';

header("Content-Type: application/json");

$response = ['success' => false, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $conn->autocommit(false); // Start transaction

    // Basic fields
    $userId = $_POST['userId'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $profession = $_POST['profession'] ?? '';
    $tagline = $_POST['tagline'] ?? '';

    // JSON fields
    $education = json_decode($_POST['education'] ?? '[]', true) ?: [];
    $certifications = json_decode($_POST['certifications'] ?? '[]', true) ?: [];
    $skills = json_decode($_POST['skills'] ?? '[]', true) ?: [];
    $jobs = json_decode($_POST['jobs'] ?? '[]', true) ?: [];
    $services = json_decode($_POST['services'] ?? '[]', true) ?: [];
    $projects = json_decode($_POST['projects'] ?? '[]', true) ?: [];

    if (empty($userId) || empty($name) || empty($email)) {
        throw new Exception("User ID, Name, and Email are required fields");
    }

    // Handle profile picture upload
    if (!empty($_FILES['profilePic']['name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/Profilein-Backend/uploads/Profile_Pics/";
        $relativeUrl = "http://localhost/Profilein-Backend/uploads/Profile_Pics/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileType = $_FILES['profilePic']['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG or GIF files are allowed");
        }

        $fileExt = pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION);
        $fileName = 'profile-' . $userId  . '.'  . $fileExt;
        $fullPath = $uploadDir . $fileName;
        $profilePicUrl = $relativeUrl . $fileName;

        if (!move_uploaded_file($_FILES['profilePic']['tmp_name'], $fullPath)) {
            throw new Exception("Failed to upload profile picture");
        }

        $stmt = $conn->prepare("UPDATE tblPersonalinfo SET ProfilePic = ? WHERE usrId = ?");
        if (!$stmt) throw new Exception("Prepare failed (profile pic): " . $conn->error);
        $stmt->bind_param("ss", $profilePicUrl, $userId);
        $stmt->execute();
        $stmt->close();
    }

    // Update personal info
    $stmt = $conn->prepare("UPDATE tblPersonalinfo SET Name = ?, Email = ?, Phone = ?, Address = ?, Profession = ?, Tagline = ? WHERE usrId = ?");
    if (!$stmt) throw new Exception("Prepare failed (personal info): " . $conn->error);
    $stmt->bind_param("sssssss", $name, $email, $phone, $address, $profession, $tagline, $userId);
    $stmt->execute();
    $stmt->close();

    // Delete existing child records
    $tables = ['tblEducation', 'tblCertification', 'tblSkill', 'tbljob', 'tblservice', 'tblProject'];
    foreach ($tables as $table) {
        $conn->query("DELETE FROM $table WHERE usrId = '$userId'");
    }

    // Re-insert updated education
    foreach ($education as $edu) {
        $stmt = $conn->prepare("INSERT INTO tblEducation (usrId, Degree_Name, Institution, Grades, Start_Year, Completion_Year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $userId, $edu['degree'], $edu['institution'], $edu['grade'], $edu['startYear'], $edu['endYear']);
        $stmt->execute();
        $stmt->close();
    }

    // Certifications
    foreach ($certifications as $cert) {
        $issueDate = !empty($cert['issueDate']) ? date('Y-m-d', strtotime($cert['issueDate'])) : null;
        $stmt = $conn->prepare("INSERT INTO tblCertification (usrId, Title, Institution, issueDate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $userId, $cert['title'], $cert['institution'], $issueDate);
        $stmt->execute();
        $stmt->close();
    }

    // Skills
    foreach ($skills as $skill) {
        $stmt = $conn->prepare("INSERT INTO tblSkill (usrId, Title, Experience) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $userId, $skill['title'], $skill['experience']);
        $stmt->execute();
        $stmt->close();
    }

    // Jobs
    foreach ($jobs as $job) {
        $startDate = !empty($job['startdate']) ? date('Y-m-d', strtotime($job['startdate'])) : null;
        $endDate = !empty($job['enddate']) ? date('Y-m-d', strtotime($job['enddate'])) : null;
        $stmt = $conn->prepare("INSERT INTO tbljob (usrId, Title, Company, Description, startDate, endDate) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $userId, $job['title'], $job['company'], $job['description'], $startDate, $endDate);
        $stmt->execute();
        $stmt->close();
    }

    // Services
    foreach ($services as $serv) {
        $stmt = $conn->prepare("INSERT INTO tblservice (usrId, Title, Description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $userId, $serv['title'], $serv['description']);
        $stmt->execute();
        $stmt->close();
    }

    // Projects
    foreach ($projects as $proj) {
        $stmt = $conn->prepare("INSERT INTO tblProject (usrId, Title, Description, Link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $userId, $proj['title'], $proj['description'], $proj['link']);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'User profile updated successfully';

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    $response['error'] = $e->getMessage();
} finally {
    $conn->autocommit(true);
    echo json_encode($response);
}
?>
