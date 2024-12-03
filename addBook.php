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
    $bookHeight = (int) $_POST['bookHeight'] ?? 100;
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
            // Check for file size (max 5MB, for example)
            if ($fileSize > 5000000) {
                echo json_encode(["status" => "error", "message" => "File size exceeds the limit of 5MB."]);
                exit();
            }

            $uploadFileDir = "/home/ngoulet/public_html/Csci487/ShelveIt/bookCovers/";
            $webAccessibleDir = "/~ngoulet/Csci487/ShelveIt/bookCovers/"; // Web-accessible directory path

            if (!is_dir($uploadFileDir)) {
                if (!mkdir($uploadFileDir, 0777, true)) {
                    echo json_encode(["status" => "error", "message" => "Failed to create upload directory."]);
                    exit();
                }
            }
            if (!is_writable($uploadFileDir)) {
                echo json_encode(["status" => "error", "message" => "Upload directory is not writable."]);
                exit();
            }

            $newFileName = uniqid() . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;
            $imagePath = $webAccessibleDir . $newFileName; // Store web-accessible path for the database

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Successfully uploaded
            } else {
                error_log("Failed to move uploaded file.");
                error_log("Source: $fileTmpPath");
                error_log("Destination: $dest_path");
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
        $stmt->bindParam(':imagePath', $imagePath);
        $stmt->bindParam(':userID', $userID);
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
