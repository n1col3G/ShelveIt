<?php
include 'db_connect.php';
session_start();
$pdo = Database::dbConnect();

// Ensure user is logged in and friendUserID is passed
if (!isset($_SESSION['user_id']) || !isset($_GET['friendUserID'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$userID = $_SESSION['user_id'];
$friendUserID = $_GET['friendUserID'];  // Passed as friend.userID

// Verify friendship with correct requester/recipient relationship
$sql = "SELECT 1 FROM Friends 
        WHERE (userID1 = :userID AND userID2 = :friendUserID) 
           OR (userID1 = :friendUserID AND userID2 = :userID)";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->bindParam(':friendUserID', $friendUserID, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // Load friend's library if they are friends
    $sql = "SELECT * FROM Books WHERE Users_UserID = :friendUserID ORDER BY shelfID, bookOrder";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':friendUserID', $friendUserID, PDO::PARAM_INT);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($books);
} else {
    echo json_encode(['error' => 'Access Denied']);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend's Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Add your custom styles here */
        .bookcase {
            width: 70%;
            height: 600px;
            margin: 30px auto;
            background-image: url('images/bookcase0_bg.jpeg');
            background-size: cover;
            border: 5px solid #8B4513;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            padding: 10px;
        }
        .shelf {
            background: #8B4513;
            border-radius: 5px;
            height: 115px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 10px;
            box-shadow: inset 0 5px 10px rgba(0,0,0,0.2), 0 5px 8px rgba(0,0,0,0.2);
        }
        .book {
            width: 50px;
            height: 100%;
            border-radius: 3px;
            cursor: not-allowed; /* Disable interaction */
            text-align: center;
            line-height: 80px;
            color: white;
            font-weight: bold;
            margin-right: 2px;
            margin-bottom: -17px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
            border: 2px solid rgba(0, 0, 0, 0.2);
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <h1 class="text-center">Friend's Library</h1>
    <div class="container">
        <div class="bookcase" id="bookcase">
            <div class="shelf" id="shelf1"></div>
            <div class="shelf" id="shelf2"></div>
            <div class="shelf" id="shelf3"></div>
            <div class="shelf" id="shelf4"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const books = <?php echo json_encode($books); ?>;
            books.forEach(book => {
                const bookElement = document.createElement('div');
                bookElement.classList.add('book');
                bookElement.style.height = `${book.bookHeight}px`;
                bookElement.style.width = `${book.bookWidth}px`;
                bookElement.style.backgroundColor = book.bookColor;

                if (book.imagePath) {
                    bookElement.style.backgroundImage = `url(${book.imagePath})`;
                    bookElement.style.backgroundSize = 'cover';
                }

                const shelfElement = document.getElementById(`shelf${book.shelfID}`);
                if (shelfElement) {
                    shelfElement.appendChild(bookElement);
                }
            });
        });
    </script>
</body>
</html>
