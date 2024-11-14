<?php
// Start or resume the session
session_start();

// Check if the user is logged in by checking if 'username' is set in the session
if (!isset($_SESSION['username'])) {
    // If the user is not logged in, redirect to the login page
    header("Location: login_user.php");
    exit();
}

// You can access session variables like 'username' to display user-specific information
// Example: echo "Welcome, " . $_SESSION['username'];
?>
