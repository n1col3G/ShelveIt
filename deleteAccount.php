<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: logout.php");
    exit();
}

// Get the current user's ID and admin status
$userID = $_SESSION['user_id'];

try {
    $pdo = Database::dbConnect();

    // Check if the current user is an admin
    $stmt = $pdo->prepare("SELECT Admin FROM Users WHERE userID = ?");
    $stmt->execute([$userID]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        // User not found, redirect to logout
        header("Location: logout.php");
        exit();
    }

    $isAdmin = $currentUser['Admin']; // 1 for admin, 0 for regular user

    // Determine target user for deletion (admin can delete others, regular users delete themselves)
    $targetUserID = isset($_POST['target_user_id']) ? intval($_POST['target_user_id']) : $userID;

    // If not an admin, ensure the user can only delete their own account
    if (!$isAdmin && $targetUserID !== $userID) {
        echo "Error: You are not authorized to delete other users.";
        exit();
    }

    // If the form is submitted to confirm the deletion
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        // Begin a transaction
        $pdo->beginTransaction();

        // Delete the user's books
        $stmt = $pdo->prepare("DELETE FROM Books WHERE Users_UserID = ?");
        $stmt->execute([$targetUserID]);

        // Delete the user from FriendRequests table (where they are either sender or recipient)
        $stmt = $pdo->prepare("DELETE FROM FriendRequests WHERE requesterID = ? OR recipientID = ?");
        $stmt->execute([$targetUserID, $targetUserID]);

        // Delete the user from Friends table (both as userID1 or userID2)
        $stmt = $pdo->prepare("DELETE FROM Friends WHERE userID1 = ? OR userID2 = ?");
        $stmt->execute([$targetUserID, $targetUserID]);

        // Delete the user's profile from Users table
        $stmt = $pdo->prepare("DELETE FROM Users WHERE userID = ?");
        $stmt->execute([$targetUserID]);

        // Commit the transaction
        $pdo->commit();

        if ($isAdmin && $targetUserID !== $userID) {
            // If the admin deletes another user, reload the page
            header("Location: adminHome.php"); // Replace with your admin page
            exit();
        } else {
            // If the user deletes their own account, log them out
            session_destroy();
            header("Location: logout.php");
            exit();
        }
    } else {
        // If the form was not submitted, redirect back
        header("Location: profile.php");
        exit();
    }
} catch (Exception $e) {
    // If something goes wrong, rollback the transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?>
