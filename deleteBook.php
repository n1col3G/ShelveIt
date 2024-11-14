<?php
session_start();
require 'db_connect.php';
$db = Database::dbConnect();

header('Content-Type: application/json');

$userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookID = $data['bookID'];

    try {
        $stmt = $db->prepare("DELETE FROM Books WHERE bookID = ? AND Users_UserID = ?");
        $stmt->execute([$bookID, $userID]);

        $stmtTime = $db->prepare("UPDATE Users SET lastEdit = NOW() WHERE UserID = ?");
        $stmtTime->execute([$userID]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
