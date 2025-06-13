<?php
include 'dbConnect.php';
include 'origins.php';

header("Content-Type: application/json");

// Initialize response array
$response = ['success' => false, 'data' => null, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $userId = trim($_GET['id']);
        
        if (empty($userId)) {
            throw new Exception("User ID is required");
        }

        // Verify database connection
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        // Prepare the response data structure
        $userData = [
            'personalInfo' => null,
            'education' => [],
            'certifications' => [],
            'skills' => [],
            'projects' => []
        ];

        // 1. Get personal info
        $query = "SELECT `infoId`, `usrId`, `Name`, `Email`, `Phone`, `Address`, `Profession`, `Tagline` FROM `tblpersonalinfo` WHERE usrId = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed for personal info: " . $conn->error);
        }
        
        $stmt->bind_param("s", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for personal info: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData['personalInfo'] = $result->fetch_assoc();
        }
        $stmt->close();

        // 2. Get education records
        $query = "SELECT * FROM tblEducation WHERE usrId = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed for education: " . $conn->error);
        }
        
        $stmt->bind_param("s", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for education: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $userData['education'][] = $row;
        }
        $stmt->close();

        // [Repeat similar pattern for certifications, skills, and projects...]
        // 3. Get certification records
        $query = "SELECT * FROM tblCertification WHERE usrId = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed for certifications: " . $conn->error);
        }
        
        $stmt->bind_param("s", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for certifications: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $userData['certifications'][] = $row;
        }
        $stmt->close();

        // 4. Get skills records
        $query = "SELECT * FROM tblSkill WHERE usrId = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed for skills: " . $conn->error);
        }
        
        $stmt->bind_param("s", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for skills: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $userData['skills'][] = $row;
        }
        $stmt->close();

        // 5. Get projects records
        $query = "SELECT * FROM tblProject WHERE usrId = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed for projects: " . $conn->error);
        }
        
        $stmt->bind_param("s", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed for projects: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $userData['projects'][] = $row;
        }
        $stmt->close();

        $response['success'] = true;
        $response['data'] = $userData;
    } else {
        throw new Exception("Invalid request method or missing ID parameter");
    }

} catch (Exception $e) {
    http_response_code(400);
    $response['error'] = $e->getMessage();
} finally {
    echo json_encode($response);
    if (isset($conn)) {
        $conn->close();
    }
}
?>