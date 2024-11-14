<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: logout.php");
    exit();
}
require 'db_connect.php';

// Enforce minimum and maximum constraints
$minWidth = 10;
$maxWidth = 100;
$minHeight = 10;
$maxHeight = 100;
$userID = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookColor = $_POST['bookColor'];
    $bookName = $_POST['bookName'] ?? 'Untitled';
    $shelfID = $_POST['shelfID'] ?? 1;
    $bookHeight = (int) $_POST['bookHeight'] ?? 100;  // Set a default value if needed
    $bookWidth = (int) $_POST['bookWidth'] ?? 50;
    $imagePath = '';
    $bookOrder = $_POST['bookOrder'];


     // Validate height and width
    if ($bookWidth < $minWidth || $bookWidth > $maxWidth) {
        echo json_encode(["status" => "error", "message" => "Book width must be between $minWidth and $maxWidth pixels."]);
        exit();
    }
    if ($bookHeight < $minHeight || $bookHeight > $maxHeight) {
        echo json_encode(["status" => "error", "message" => "Book height must be between $minHeight and $maxHeight pixels."]);
        exit();
    }

    // Handle file upload
    if (isset($_FILES['bookCover']) && $_FILES['bookCover']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bookCover']['tmp_name'];
        $fileName = $_FILES['bookCover']['name'];
        $fileSize = $_FILES['bookCover']['size'];
        $fileType = $_FILES['bookCover']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Allowed file extensions
        $allowedfileExtensions = ['jpg', 'png', 'jpeg', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Directory where images will be stored
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the uploaded file
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imagePath = $dest_path; // Store the file path
            } else {
                echo json_encode(["status" => "error", "message" => "Error moving the file."]);
                exit();
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Only image files are allowed."]);
            exit();
        }
    }

    // Insert book details into the database
    try {
        $conn = Database::dbConnect();
        $query = "INSERT INTO Books (bookName, bookColor, shelfID, imagePath, Users_UserID, bookOrder, bookHeight, bookWidth) 
        VALUES (:bookName, :bookColor, :shelfID, :imagePath, :userID, :bookOrder, :bookHeight, :bookWidth)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':bookName', $bookName);
        $stmt->bindParam(':bookColor', $bookColor);
        $stmt->bindParam(':shelfID', $shelfID);
        $stmt->bindParam(':imagePath', $imagePath); // Save the file path in the database
        $stmt->bindParam(':userID', $userID); // Assuming you have user session
        $stmt->bindParam(':bookOrder', $bookOrder);
        $stmt->bindParam(':bookHeight', $bookHeight);
        $stmt->bindParam(':bookWidth', $bookWidth);
        $stmt->execute();
        
        $stmtTime = $conn->prepare("UPDATE Users SET lastEdit = NOW() WHERE UserID = ?");
        $stmtTime->execute([$userID]);

        echo json_encode(["status" => "success", "message" => "Book added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}
?>
