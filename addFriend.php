<?php
include 'db_connect.php';
session_start();
$pdo = Database::dbConnect();

//Enable error reporting (for development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

//Decode the JSON input
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

//Check if data was decoded correctly
if (!$data) {
    echo json_encode(['status' => 'Error: Invalid JSON input']);
    exit;
}

//Retrieve data from the decoded JSON
$requesterID = $_SESSION['user_id'];
$recipientID = $data['recipientID']; 

if (!$requesterID || !$recipientID) {
    echo json_encode(['status' => 'Error: Missing UserID or recipientID']);
    exit;
}

//Creating the friend request in the database
$sql = "INSERT INTO FriendRequests (requesterID, recipientID, status) VALUES (:requesterID, :recipientID, 'Pending')";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':requesterID', $requesterID, PDO::PARAM_INT);
$stmt->bindParam(':recipientID', $recipientID, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(['status' => 'Request Sent']);
} else {
    echo json_encode(['status' => 'Error']);
}
exit;
?>
