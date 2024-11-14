<?php
// Start session to access the logged-in user's ID
session_start();

// Include the database connection
require_once 'db_connect.php';
$db = Database::dbConnect();

// Check if the user is logged in and if 'bookID' is provided
if (isset($_SESSION['user_id']) && isset($_GET['bookID'])) {
    $userID = $_SESSION['user_id']; // Logged-in user's ID
    $bookID = $_GET['bookID'];

    try {
        // Prepare the SQL query to get book details for the logged-in user
        $stmt = $db->prepare("SELECT * FROM Books WHERE bookID = :bookID AND Users_UserID = :userID");
        $stmt->bindParam(':bookID', $bookID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the book details
        $bookDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if a book was found
        if ($bookDetails) {
            // Send the book details as JSON
            echo json_encode($bookDetails);
        } else {
            // If no book is found, send an error message
            echo json_encode(['error' => 'Book not found or you do not have access to this book']);
        }
    } catch (PDOException $e) {
        // Handle any errors
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // If user is not logged in or 'bookID' is not set, return an error
    echo json_encode(['error' => 'You must be logged in and provide a valid bookID']);
}
?>
