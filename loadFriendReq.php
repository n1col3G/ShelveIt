<?php
include 'db_connect.php';
session_start();
$pdo = Database::dbConnect();

//Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('User is not logged in');
}

$userID = $_SESSION['user_id'];

//SQL to fetch friend requests where the logged-in user is the recipient
$sql = "SELECT fr.requestID, u.UserID, u.Firstname, u.Lastname, fr.requestDate, fr.status
        FROM FriendRequests fr
        JOIN Users u ON u.UserID = fr.requesterID
        WHERE fr.recipientID = :userID
        ORDER BY fr.requestDate DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();

//Fetch all the friend requests
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

//If no requests are found, return an empty array
if (!$requests) {
    echo json_encode([]);
    exit;
}

//Return the friend requests as a JSON response
echo json_encode($requests);
?>
