<?php
include 'db_connect.php';
session_start();
$pdo = Database::dbConnect();

//Ensure user is logged in and friendUserID is passed
if (!isset($_SESSION['user_id']) || !isset($_GET['friendUserID'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$userID = $_SESSION['user_id'];
$friendUserID = $_GET['friendUserID'];  //Passed as friend.userID

//Verify friendship with correct requester/recipient relationship
$sql = "SELECT 1 FROM Friends 
        WHERE (userID1 = :userID AND userID2 = :friendUserID) 
           OR (userID1 = :friendUserID AND userID2 = :userID)";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->bindParam(':friendUserID', $friendUserID, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    //Load friend's library if they are friends
    $sql = "SELECT * FROM Books WHERE Users_UserID = :friendUserID ORDER BY shelfID, bookOrder";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':friendUserID', $friendUserID, PDO::PARAM_INT);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Query for friend's first name
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
    <link href="css/styles.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/bkg7.jpeg');
            width: 100%;
            height: 100%;
            background-size: 111%;
            background-position: center;
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
        //Looping through the books to show like the homepage
        document.addEventListener('DOMContentLoaded', function() {
            const books = <?php echo json_encode($books); ?>;
            books.forEach(book => {
                const bookElement = document.createElement('div');
                bookElement.classList.add('book');
                bookElement.style.height = `${book.bookHeight}px`;
                bookElement.style.width = `${book.bookWidth}px`;
                bookElement.style.backgroundColor = book.bookColor;

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

        //Adjust the textsize for larger titles and smaller books
        function adjustTextSize(bookElement) {
            const titleElement = bookElement.querySelector('.book-title');
            const parentWidth = bookElement.style.height;
            const parentHeight = bookElement.style.width;

            let fontSize = 16; //Start with a base font size
            titleElement.style.fontSize = `${fontSize}px`;

            while (
                fontSize > 10 && 
                (titleElement.scrollWidth > parentWidth || titleElement.scrollHeight > parentHeight)
            ) {
                fontSize -= 1; //Reduce font size
                titleElement.style.fontSize = `${fontSize}px`;
            }

            if (fontSize === 10 && (titleElement.scrollWidth > parentWidth || titleElement.scrollHeight > parentHeight)) {
                titleElement.style.whiteSpace = 'normal'; //Wrap text if it still doesn't fit
            }
        }
    </script>
</body>
</html>
