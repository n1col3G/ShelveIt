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

    // Query for friend's first name
    $sql = "SELECT Firstname FROM Users WHERE UserID = :friendUserID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':friendUserID', $friendUserID, PDO::PARAM_INT);
    $stmt->execute();
    $friendName = $stmt->fetchColumn();
} else {
    $books = [];
    $firstName = 'Unknown';
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
        body {
            /*background-color: #f0f0f0;*/
            background-image: url('images/bkg7.jpeg');
            width: 100%;
            height: 100%;
            background-size: 111%;
            background-position: center;
        }
        .bookcase {
            width: 70%;
            height: 600px;
            margin: 30px auto;
            background-image: url('images/bookcase0_bg.jpeg');
            background-size: cover;
            border: 1px solid #664024;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            padding: 10px;
        }
        .shelf {
            background: #664024;
            border-radius: 5px;
            height: 115px;
            display: flex;
            justify-content: flex-start;
            align-items: flex-end;
            padding: 10px;
            box-shadow: inset 0 5px 10px rgba(0,0,0,0.2), 0 5px 8px rgba(0,0,0,0.2);
            position: relative;
        }
        .book {
            /*width: 50px;
            height: 100%;*/
            border-radius: 3px;
            cursor: pointer;
            /*text-align: center;*/
            /*line-height: 80px;*/
            color: white;
            font-weight: bold;
            margin-right: 2px;
            margin-bottom: -9px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
            border: 2px solid rgba(0, 0, 0, 0.2);
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            border-radius: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }
        .heading-image {
            height: 70px; /* Adjust the height of the image */
            width: auto; /* Let the width adjust proportionally */
            margin-left: 5px;
        }
        .book-title {
            font-size: 16px;  /*Default size */
            /*font-size: calc(10px + 0.5vw);  Responsive scaling */
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 90%; /* Keep some padding from edges */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row align-items-center mt-2">
            <div class="col-6">
                <img src="images/ShelveIt-01.png" alt="Image" class="heading-image">
            </div>
            <div class="col-6 d-flex justify-content-end">
                <button class="btn btn-secondary me-2" onclick="window.location.href='userHome.php';">Home</button>
                <button class="btn btn-primary" onclick="window.location.href='logout.php';">Logout</button>
            </div>
        </div>
    </div>

    <h3 class="text-center"><?php echo htmlspecialchars($friendName); ?>'s Library</h3>
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

                // Add the book title inside the book element
                //bookElement.textContent = book.bookName;

                const titleElement = document.createElement('span');
                titleElement.classList.add('book-title');
                titleElement.innerText = book.bookName;
                bookElement.appendChild(titleElement);
                adjustTextSize(bookElement); 

                if (book.imagePath) {
                    bookElement.style.backgroundImage = `url(${book.imagePath})`;
                    bookElement.style.backgroundSize = 'cover';
                    bookElement.style.backgroundColor = 'transparent';
                }

                const shelfElement = document.getElementById(`shelf${book.shelfID}`);
                if (shelfElement) {
                    shelfElement.appendChild(bookElement);
                }
            });
        });

        function adjustTextSize(bookElement) {
            const titleElement = bookElement.querySelector('.book-title');
            const parentWidth = bookElement.style.height;
            const parentHeight = bookElement.style.width;

            let fontSize = 16; // Start with a base font size
            titleElement.style.fontSize = `${fontSize}px`;

            while (
                fontSize > 10 && 
                (titleElement.scrollWidth > parentWidth || titleElement.scrollHeight > parentHeight)
            ) {
                fontSize -= 1; // Reduce font size
                titleElement.style.fontSize = `${fontSize}px`;
            }

            if (fontSize === 10 && (titleElement.scrollWidth > parentWidth || titleElement.scrollHeight > parentHeight)) {
                titleElement.style.whiteSpace = 'normal'; // Wrap text if it still doesn't fit
            }
        }
    </script>
</body>
</html>
