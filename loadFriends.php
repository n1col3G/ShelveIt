<?php
include 'db_connect.php';
session_start();
$pdo = Database::dbConnect();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'Error: User not logged in']);
    exit;
}

$userID = $_SESSION['user_id'];

try {
    // SQL query to select friends excluding the current user from results
    $sql = "SELECT u.UserID, u.Firstname, u.Lastname, f.friendshipDate, f.friendID 
            FROM Friends f
            JOIN Users u ON u.UserID = CASE 
                WHEN f.userID1 = :userID THEN f.userID2 
                WHEN f.userID2 = :userID THEN f.userID1 
            END
            WHERE (f.userID1 = :userID OR f.userID2 = :userID)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    
    // Execute the query and fetch results
    $stmt->execute();
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return friends list as JSON response
    if ($friends === false || empty($friends)) {
        error_log("No friends found or query error.");
        echo json_encode([]);
        exit;
    } else {
        error_log("Friends found: " . json_encode($friends));
        echo json_encode($friends);
        exit;
    }    
    
} catch (PDOException $e) {
    // Return detailed error information in case of a database error
    echo json_encode(['status' => 'Database error: ' . $e->getMessage()]);
}
?>
