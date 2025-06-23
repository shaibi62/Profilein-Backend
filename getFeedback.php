<?php
include 'origins.php';
include 'dbConnect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $sql = "
                SELECT 
                    fdb.fdbId,
                    fdb.usrId,
                    fdb.Star,
                    fdb.Content,
                    fdb.DateSubmitted,
                    COALESCE(pinfo.Name, u.Name) AS Name,
                    pinfo.Profession,
                    pinfo.ProfilePic
                FROM tblfeedback fdb
                LEFT JOIN tblpersonalinfo pinfo ON fdb.usrId = pinfo.usrId
                LEFT JOIN tbluser u ON fdb.usrId = u.usrId
                ORDER BY fdb.DateSubmitted DESC
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            $feedback = [];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Apply manual fallback if pinfo.Profession/ProfilePic is null
                    $row['Profession'] = $row['Profession'] ?? 'User';
                    $row['ProfilePic'] = $row['ProfilePic'] ?? 'default-profile.png';
                    $feedback[] = $row;
                }
                echo json_encode(["success" => true, "data" => $feedback]);
            } else {
                echo json_encode(["success" => false, "message" => "No feedback found"]);
            }

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Unexpected error: " . $e->getMessage(),
                "code" => "SERVER_ERROR"
            ]);
        }
        break;
}
?>
