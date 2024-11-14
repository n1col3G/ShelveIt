<?php
// Start session to access the logged-in user's ID
session_start();

// Include the database connection
require_once 'db_connect.php';
$db = Database::dbConnect();

// Check if the user is logged in, and if the bookID is provided via POST
if (isset($_SESSION['user_id']) && isset($_POST['bookID'])) {
    $userID = $_SESSION['user_id']; // Logged-in user's ID
    $bookID = $_POST['bookID'];

    // Get the updated book details from the POST request
    $bookColor = $_POST['bookColor'];
    $bookName = $_POST['bookName'];
    $bookHeight = (int) $_POST['bookHeight'];
    $bookWidth = (int) $_POST['bookWidth'];

    try {
        // Check if the book belongs to the logged-in user
        $checkStmt = $db->prepare("SELECT * FROM Books WHERE bookID = :bookID AND Users_UserID = :userID");
        $checkStmt->bindParam(':bookID', $bookID, PDO::PARAM_INT);
        $checkStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $checkStmt->execute();

        // If the book is found and belongs to the user, update the book
        if ($checkStmt->rowCount() > 0) {
            $updateQuery = "UPDATE Books 
                            SET bookColor = :bookColor, bookName = :bookName, bookWidth = :bookWidth, bookHeight = :bookHeight 
                            WHERE bookID = :bookID AND Users_UserID = :userID";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':bookColor', $bookColor);
            $updateStmt->bindParam(':bookName', $bookName);
            $updateStmt->bindParam(':bookWidth', $bookWidth);
            $updateStmt->bindParam(':bookHeight', $bookHeight);
            $updateStmt->bindParam(':bookID', $bookID, PDO::PARAM_INT);
            $updateStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            $updateStmt->execute();

            $stmtTime = $db->prepare("UPDATE Users SET lastEdit = NOW() WHERE UserID = ?");
            $stmtTime->execute([$userID]);

            echo json_encode(['success' => 'Book updated successfully']);
        } else {
            echo json_encode(['error' => 'You do not have permission to edit this book']);
        }
    } catch (PDOException $e) {
        // Handle any errors
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // If the user is not logged in or bookID is not set, return an error
    echo json_encode(['error' => 'You must be logged in and provide a valid bookID']);
}
?>
