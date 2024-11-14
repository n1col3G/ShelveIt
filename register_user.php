<?php
// Include database connection
include 'db_connect.php';
session_start(); // Start the session if needed

// Function to generate a unique 5-digit UserID
function generateUniqueUserID($conn) {
    $unique = false;
    $userID = null;
    
    while (!$unique) {
        // Generate a random 5-digit number
        $userID = rand(10000, 99999);

        // Check if this UserID already exists in the database
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE UserID = :userID");
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();
        
        $count = $stmt->fetchColumn();
        if ($count == 0) {
            // If the count is 0, it means this UserID is unique
            $unique = true;
        }
    }

    return $userID;
}

// Get form data
$firstName = $_POST['firstname'];
$lastName = $_POST['lastname'];
$email = $_POST['email'];
$password = $_POST['password'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Connect to the database
$conn = Database::dbConnect();

// Generate a unique 5-digit UserID
$userID = generateUniqueUserID($conn);

// Insert the new user into the database
$sql = "INSERT INTO Users (UserID, Email, Password, Admin, Lastname, Firstname) VALUES (:userID, :email, :password, :admin, :lastname, :firstname)";
$stmt = $conn->prepare($sql);

$admin = 0; // Non-admin user, represented as 0

$stmt->bindParam(':userID', $userID);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $hashedPassword);
$stmt->bindParam(':admin', $admin);
$stmt->bindParam(':lastname', $lastName);
$stmt->bindParam(':firstname', $firstName);

if ($stmt->execute()) {
    // Registration successful, redirect to the login page
    $_SESSION['successMessage'] = "Account created successfully. Please log in.";
    header("Location: index.php?success=accountcreated");
    exit();
} else {
    echo "Error creating account. Please try again.";
}

$conn = null; // Close the connection
?>
