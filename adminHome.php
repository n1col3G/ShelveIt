<?php
session_start();
require 'db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = Database::dbConnect();

if (!isset($_SESSION['email']) || ($_SESSION['admin'] !== 1)) {
    header("Location: logout.php");
    exit();
}

//Fetch users from the database
try {
    $stmt = $db->prepare("
        SELECT u.*, COUNT(b.bookID) AS book_count 
        FROM Users u 
        LEFT JOIN Books b ON u.UserID = b.Users_UserID 
        WHERE u.Admin != 1 
        GROUP BY u.UserID
    ");    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

//Handle user deletion
if (isset($_POST['delete_user'])) {
    $userIdToDelete = $_POST['user_id'];
    try {
        $stmt = $db->prepare("DELETE FROM Users WHERE UserID = ?"); //Adjust the UserID field if necessary
        $stmt->execute([$userIdToDelete]);
        header("Location: admin.php"); //Redirect back to the admin page after deletion
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_POST['delete_book'])) {
    $bookIdToDelete = $_POST['book_id'];
    try {
        $stmt = $db->prepare("DELETE FROM Books WHERE BookID = ?"); //Adjust the BookID field if necessary
        $stmt->execute([$bookIdToDelete]);
        header("Location: admin.php"); //Redirect back to the admin page after deletion
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelveIt! -- Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f0f0;
        }
        .heading {
            margin-left: 0px; 
            padding: 10px 0;
            border-left: 5px;
            border-right: 5px;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .heading-content {
            display: flex;
            align-items: center;
            width: 100%;
            margin-left: 20px;
        }
        .heading-image {
            height: 70px; /* Adjust the height of the image */
            width: auto; /* Let the width adjust proportionally */
            margin-left: 5px;
        }
        .navbar {
            padding: 10px;
        }
        .navbar-buttons {
            display: flex;
            justify-content: flex-end;
            width: 100%;
        }
        .navbar-buttons button {
            margin-right: 10px;
            margin-top: 10px;
        }
        .user-folder {
            cursor: pointer;
            padding: 10px;
            background-color: #ffffff;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .user-books {
            display: none; /* Hidden by default */
            margin-left: 20px;
        }
        .delete-folder {
            position: absolute;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            color: #e74c3c;
            cursor: pointer;
        }
        .text-muted{
            color:rgba(138, 133, 133, 0.864);
            font-style: italic;
            margin-left: 7px;
        }
    </style>
</head>
<body>
    <!-- Navbar Header with Heading and Buttons -->
    <div class="container-fluid">
        <div class="row align-items-center mt-2">
            <div class="col-6">
                <img src="images/ShelveIt-01.png" alt="Image" class="heading-image">
            </div>
            <div class="col-6 d-flex justify-content-end">
                <button class="btn btn-secondary me-2" onclick="window.location.href='profile.php';">Profile</button>
                <button class="btn btn-primary" onclick="window.location.href='logout.php';">Logout</button>
            </div>
        </div>
    </div>

    <div class="container mt-4" style="width: 960px;">
        <h3>Registered Users</h3>
        <div id="userList">
            <?php foreach ($users as $user): ?>
                <div class="user-folder" onclick="toggleBooks(<?= $user['UserID']; ?>)">
                    <?= htmlspecialchars($user['Firstname'] . ' ' . $user['Lastname'] . '  -  ' . $user['Email']); ?>
                    <span class="text-muted">(Last Updated: <?= htmlspecialchars($user['lastEdit'] ?: 'N/A'); ?>, <?= $user['book_count']; ?> books)</span>
                    <span id="toggle-icon-<?= $user['UserID']; ?>" class="fas fa-chevron-down" style="cursor:pointer; float: left; margin-right: 10px; margin-left: 2px; margin-top: 3px;"></span>
               
                    <form method="POST" action="deleteAccount.php" style="display:inline;">
                        <input type="hidden" name="target_user_id" value="<?= $user['UserID']; ?>">
                        <input type="hidden" name="confirm_delete" value="yes">
                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm float-end" onclick="return confirm('Are you sure you want to delete this user?');">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <div class="user-books" id="books-<?= $user['UserID']; ?>">
                    <?php
                    //Fetch books for each user
                    $stmt = $db->prepare("SELECT * FROM Books WHERE Users_UserID = ? ORDER BY BookID");
                    $stmt->execute([$user['UserID']]);
                    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                     <?php if (count($books) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($books as $book): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($book['bookName']); ?>
                                    <form method="POST" action="deleteBook.php" style="display:inline;">
                                        <input type="hidden" name="target_book_id" value="<?= $book['bookID']; ?>">
                                        <input type="hidden" name="confirm_delete" value="yes">
                                        <button type="submit" name="delete_book" class="btn btn-sm" onclick="return confirm('Are you sure you want to delete this book?');">
                                            <i class="fas fa-square-minus" style="color: #e74c3c;"></i>
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>This user has no books on their bookshelf.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleBooks(userId) {
            const booksContainer = document.getElementById('books-' + userId);
            const toggleIcon = document.getElementById('toggle-icon-' + userId);

            if (booksContainer.style.display === "none" || booksContainer.style.display === "") {
            booksContainer.style.display = "block"; //Show the books
            toggleIcon.classList.remove('fa-chevron-down'); //Remove down icon
            toggleIcon.classList.add('fa-chevron-up'); //Add up icon
            } else {
                booksContainer.style.display = "none"; //Hide the books
                toggleIcon.classList.remove('fa-chevron-up'); //Remove up icon
                toggleIcon.classList.add('fa-chevron-down'); //Add down icon
            }
        }
        function confirmDelete() {
            if (confirm("Are you sure you want to delete this account? This action cannot be undone.")) {
                document.getElementById("deleteForm").submit();
            }
        }
    </script>
</body>
</html>
