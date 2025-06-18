<?php
header('Content-Type: application/json');
include 'origins.php';
include 'dbConnect.php';
if(!isset($_GET['id']))
{
    echo json_encode(['success' => false, 'message' => 'user not loggedin/ id not found']);
    return;

}
$usrId = $_GET['id'];
$query = "SELECT 
    pf.prtId,
    u.Name AS userName,
    t.Title AS templateName,
    pf.portfolioLink
FROM tblportfolio pf
JOIN tbluser u ON pf.usrId = u.usrId
JOIN tbltemplate t ON pf.tmpId = t.tmpId 
WHERE pf.usrId = '$usrId'
ORDER BY pf.prtId DESC";

$result = mysqli_query($conn, $query);

$portfolios = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $portfolios[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $portfolios]);
} else {
    echo json_encode(['success' => false, 'message' => 'Query failed']);
}

mysqli_close($conn);
?>
