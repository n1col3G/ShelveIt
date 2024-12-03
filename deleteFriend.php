<?php
session_start();
require 'db_connect.php'; // Ensure correct path

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: logout.php");
    exit();
}

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Log the received data
file_put_contents('php://stderr', print_r($data, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($data['friendID']) || empty($data['friendID'])) {
        echo json_encode(['status' => 'Error', 'message' => 'Friend ID is required']);
        exit;
    }
    $friendID = $data['friendID'];

    try {
        $pdo = Database::dbConnect();
        $stmt = $pdo->prepare("DELETE FROM Friends WHERE friendID = :friendID");
        $stmt->bindParam(':friendID', $friendID, PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'Success', 'message' => 'Friendship deleted successfully.']);
            } else {
                echo json_encode(['status' => 'Error', 'message' => 'No friendship found with the given ID.']);
            }
        } else {
            echo json_encode(['status' => 'Error', 'message' => 'Failed to delete friendship']);
        }
        Database::dbDisconnect();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'Error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'Error', 'message' => 'Invalid request method']);
}
?>
