<?php
session_start();
require 'db_connect.php';
$db = Database::dbConnect();

header('Content-Type: application/json');

$userID = $_SESSION['user_id'];

// Fetch admin status
$stmt = $db->prepare("SELECT Admin FROM Users WHERE UserID = ?");
$stmt->execute([$userID]);
$isAdmin = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookID = isset($data['bookID']) ? intval($data['bookID']) : null;
    $targetBookID = isset($_POST['target_book_id']) ? intval($_POST['target_book_id']) : null;

    $bookToDelete = $isAdmin ? $targetBookID : $bookID;

    if (!$bookToDelete) {
        echo json_encode(['success' => false, 'error' => 'Invalid book ID.']);
        exit();
    }

    try {
        if (!$isAdmin) {
            // Regular users can delete only their own books
            $stmt = $db->prepare("DELETE FROM Books WHERE bookID = ? AND Users_UserID = ?");
            $stmt->execute([$bookToDelete, $userID]);
        } else {
            // Admins can delete any book
            $stmt = $db->prepare("DELETE FROM Books WHERE bookID = ?");
            $stmt->execute([$bookToDelete]);
            // Redirect to adminHome.php
            header('Location: adminHome.php');
            exit();
        }

        // Update lastEdit for the user who owns the book (if applicable)
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
