<?php
include 'origins.php';
include 'dbConnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM template WHERE Template_ID = $id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $template = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'template' => $template]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Template not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

mysqli_close($conn);
?>
