<?php
//Start session to access the logged-in user's ID
session_start();

//Include the database connection
require_once 'db_connect.php';
$db = Database::dbConnect();

//Check if the user is logged in, and if the bookID is provided via POST
if (isset($_SESSION['user_id']) && isset($_POST['bookID'])) {
    $userID = $_SESSION['user_id']; // Logged-in user's ID
    $bookID = $_POST['bookID'];

    //Get the updated book details from the POST request
    $bookColor = $_POST['bookColor'];
    $bookName = $_POST['bookName'];
    $bookHeight = (int) $_POST['bookHeight'];
    $bookWidth = (int) $_POST['bookWidth'];
    $deleteImage = isset($_POST['deleteImage']) && $_POST['deleteImage'] === 'true';

    //Initialize variables for image handling
    $newImagePath = null;

    try {
        //Check if the book belongs to the logged-in user
        $checkStmt = $db->prepare("SELECT * FROM Books WHERE bookID = :bookID AND Users_UserID = :userID");
        $checkStmt->bindParam(':bookID', $bookID, PDO::PARAM_INT);
        $checkStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $checkStmt->execute();

        //If the book is found and belongs to the user, update the book
        if ($checkStmt->rowCount() > 0) {
            $bookData = $checkStmt->fetch(PDO::FETCH_ASSOC);  // Fetch the book details once
            $uploadFileDir = "/home/ngoulet/public_html/Csci487/ShelveIt/bookCovers/";
            $webAccessibleDir = "/~ngoulet/Csci487/ShelveIt/bookCovers/";
            $existingImageServerPath = str_replace($webAccessibleDir, $uploadFileDir, $bookData['imagePath']);

            //Handle image deletion if requested
            if ($deleteImage) {
                //Delete the existing image from the filesystem
                if (!empty($existingImageServerPath) && file_exists($existingImageServerPath)) {
                    unlink($existingImageServerPath);
                }
                $newImagePath = null; //Ensure imagePath is set to null for database update
            } elseif (isset($_FILES['bookCover']) && $_FILES['bookCover']['error'] === UPLOAD_ERR_OK) {
                //Handle file upload for new book cover
                $fileTmpPath = $_FILES['bookCover']['tmp_name'];
                $fileName = $_FILES['bookCover']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $allowedfileExtensions = ['jpg', 'png', 'jpeg', 'gif'];
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0777, true);
                    }

                    $newFileName = uniqid() . '.' . $fileExtension;
                    $destPath = $uploadFileDir . $newFileName;
                    $newImagePath = $webAccessibleDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        //Remove the old image if it exists
                        if (!empty($existingImageServerPath) && file_exists($existingImageServerPath)) {
                            unlink($existingImageServerPath);
                        }
                    } else {
                        echo json_encode(["error" => "Error moving the uploaded file."]);
                        exit();
                    }
                } else {
                    echo json_encode(["error" => "Only image files are allowed."]);
                    exit();
                }
            }

            $updateQuery = "UPDATE Books 
                SET bookColor = :bookColor, bookName = :bookName, bookWidth = :bookWidth, bookHeight = :bookHeight";
                if ($deleteImage || isset($_FILES['bookCover'])) {
                    $updateQuery .= ", imagePath = :imagePath";
                }
                $updateQuery .= " WHERE bookID = :bookID AND Users_UserID = :userID";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':bookColor', $bookColor);
            $updateStmt->bindParam(':bookName', $bookName);
            $updateStmt->bindParam(':bookWidth', $bookWidth);
            $updateStmt->bindParam(':bookHeight', $bookHeight);
            if ($deleteImage || isset($_FILES['bookCover'])) {
                $updateStmt->bindParam(':imagePath', $newImagePath);
            }
            $updateStmt->bindParam(':bookID', $bookID, PDO::PARAM_INT);
            $updateStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            //Debugging
            error_log("SQL Query: " . $updateQuery);
            error_log("Parameters: " . json_encode([
                'bookColor' => $bookColor,
                'bookName' => $bookName,
                'bookWidth' => $bookWidth,
                'bookHeight' => $bookHeight,
                'imagePath' => $newImagePath,
                'bookID' => $bookID,
                'userID' => $userID
            ]));
            $updateStmt->execute();

            $stmtTime = $db->prepare("UPDATE Users SET lastEdit = NOW() WHERE UserID = ?");
            $stmtTime->execute([$userID]);

            echo json_encode(['success' => 'Book updated successfully']);
        } else {
            echo json_encode(['error' => 'You do not have permission to edit this book']);
        }
    } catch (PDOException $e) {
        //Handle any errors
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    //If the user is not logged in or bookID is not set, return an error
    echo json_encode(['error' => 'You must be logged in and provide a valid bookID']);
}
?>
