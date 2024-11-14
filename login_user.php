<?php
session_start();  // Start the session

include 'db_connect.php'; // Include your database connection

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connect to the database
    $conn = Database::dbConnect();

    // Fetch the user from the database
    $sql = "SELECT * FROM Users WHERE Email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if (password_verify($password, $user['Password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['UserID'];  // Store unique UserID
            $_SESSION['firstName'] = $user['Firstname'];  // Store first name
            $_SESSION['lastName'] = $user['Lastname'];  // Store last name
            $_SESSION['email'] = $user['Email'];  // Store email
            $_SESSION['admin'] = $user['Admin'];  // Store admin status (boolean)

            if ($user['Admin'] === 1) {
                header("Location: adminHome.php");
            } else {
                header("Location: userHome.php");
            }
            // Redirect to the user's home page
            //header("Location: userHome.php");
            exit();
        } else {
            $_SESSION['errorMessage'] = "Incorrect email or password.";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['errorMessage'] = "No user found with this email.";
        header("Location: index.php");
        exit;
    }

    $conn = null;  // Close the connection
}
?>
