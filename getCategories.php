<?php
include 'origins.php';
include 'dbConnect.php';
header("Content-Type: application/json");
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $query = "SELECT DISTINCT category FROM tbltemplate";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                throw new Exception("Database query failed: " . mysqli_error($conn));
            }

            $categories = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $categories[] = ['name' => $row['category'], 'id' => $row['category']];
            }

            echo json_encode(['success' => true, 'categories' => $categories]);
        } else {
            throw new Exception("Invalid request method");
        }
        {

        }

    }
catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

?>