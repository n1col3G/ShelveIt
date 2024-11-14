<?php
include 'db_connect.php';
session_start();
$pdo = Database::dbConnect();

// Decode the JSON input and check for errors
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'Error: Invalid JSON format']);
    exit;
}

$requestID = $data['requestID'];
$status = $data['status'];

if (!$requestID || !$status) {
    echo json_encode(['status' => 'Error: Missing requestID or status']);
    exit;
}

try {
    // Update the request status
    $sql = "UPDATE FriendRequests SET status = :status WHERE requestID = :requestID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($status === 'Accepted') {
            // Retrieve the requester and recipient IDs
            $sql = "SELECT requesterID, recipientID FROM FriendRequests WHERE requestID = :requestID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
            $stmt->execute();
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($request) {
                $userID1 = $request['requesterID'];
                $userID2 = $request['recipientID'];

                // Insert into Friends table
                $sql = "INSERT INTO Friends (requestID, userID1, userID2) VALUES (:requestID, :userID1, :userID2)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
                $stmt->bindParam(':userID1', $userID1, PDO::PARAM_INT);
                $stmt->bindParam(':userID2', $userID2, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'Request Updated']);
                } else {
                    echo json_encode(['status' => 'Error: Could not insert into Friends']);
                }
            } else {
                echo json_encode(['status' => 'Error: Request not found']);
            }
        } else {
            echo json_encode(['status' => 'Request Updated']);
        }
    } else {
        echo json_encode(['status' => 'Error updating request status']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'Database error: ' . $e->getMessage()]);
}
?>
