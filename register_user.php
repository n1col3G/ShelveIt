<?php
include 'db_connect.php'; // Database connection

// Get user input from form
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

// Insert user into Users table
$sql_user = "INSERT INTO Users (Username, Password, TypeID, Admin) VALUES ('$email', '$password', UUID(), 0)";

if ($conn->query($sql_user) === TRUE) {
    $user_type_id = $conn->insert_id;

    // Insert user profile into Profiles table
    $sql_profile = "INSERT INTO Profiles (Lastname, Firstname, Users_TypeID) VALUES ('$lastname', '$firstname', '$user_type_id')";

    if ($conn->query($sql_profile) === TRUE) {
        header("Location: userHome.html"); // Redirect to user home after successful registration
        exit();
    } else {
        echo "Error: " . $sql_profile . "<br>" . $conn->error;
    }
} else {
    echo "Error: " . $sql_user . "<br>" . $conn->error;
}

$conn->close();
?>
