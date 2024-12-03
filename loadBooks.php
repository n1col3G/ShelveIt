<?php
session_start();
require 'db_connect.php';

$db = Database::dbConnect();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]); // Return an empty array if no user ID is found
    exit();
}

$userID = $_SESSION['user_id']; // Get the current user's ID

// Fetch books from the database
try {
    $stmt = $db->prepare("SELECT * FROM Books WHERE Users_UserID = ? ORDER BY shelfID, bookOrder");
    $stmt->execute([$userID]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if (empty($books)) {
        echo json_encode(["message" => "No books found for user ID: " . $userID]); // Debugging output
    } else {
        echo json_encode($books); // Return books as JSON
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]); // Debugging output
}
?>
