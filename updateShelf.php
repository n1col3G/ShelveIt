<?php
session_start();
require 'db_connect.php';

$db = Database::dbConnect();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// Function to reassign book orders on a specific shelf
function reassignBookOrders($db, $shelfID) {
    // Fetch all books on the specified shelf and order them by their current bookOrder
    $stmt = $db->prepare("SELECT bookID FROM Books WHERE shelfID = ? ORDER BY bookOrder");
    $stmt->execute([$shelfID]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update the bookOrder for each book
    foreach ($books as $index => $book) {
        $newOrder = $index + 1; // Book order starts from 1
        $updateStmt = $db->prepare("UPDATE Books SET bookOrder = ? WHERE bookID = ?");
        $updateStmt->execute([$newOrder, $book['bookID']]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookID = $data['bookID'];
    $newShelfID = $data['shelfID'];
    $bookOrder = $data['bookOrder'];

    if (isset($bookID, $newShelfID, $bookOrder)) {
        try {
            $stmt = $db->prepare("UPDATE Books SET shelfID = ?, bookOrder = ? WHERE bookID = ? AND Users_UserID = ?");
            $stmt->execute([$newShelfID, $bookOrder, $bookID, $_SESSION['user_id']]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
}
?>
