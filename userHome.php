<?php
    session_start();
    require 'db_connect.php';
    error_reporting(E_ALL); // Report all errors
    ini_set('display_errors', 1); // Display errors on the page

    $db = Database::dbConnect();
    

    if (!isset($_SESSION['email'])) {
        header("Location: logout.php");
        exit();
    }

    $userID = $_SESSION['user_id']; // Get the current user's ID

    // Fetch books from database
    try {
        $stmt = $db->prepare("SELECT * FROM Books WHERE Users_UserID = ? ORDER BY bookOrder");
        $stmt->execute([$userID]);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

    include 'friendModal.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelveIt!</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom CSS for the background -->
    <link href="css/styles.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/bkg7.jpeg');
            width: 100%;
            height: 100%;
            background-size: 111%;
            background-position: center;
        }
        /* Customization for "Add Book" button in bottom right corner */
        .add-book-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .add-friend-btn{
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
        }
        .navbar-buttons button {
            margin-right: 10px;
            margin-top: 10px;
        }
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-title {
            font-weight: bold;
        }
        #bookCustomizationForm .form-label {
            font-weight: bold;
        }
        /* Adjusting the size of the color input (book color preview) */
        input[type="color"].form-control {
            width: 40px;  /* Change width */
            height: 40px; /* Change height */
            padding: 0;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="file"].form-control {
            width: 55%;  /* Change width */
            height: 30px; /* Change height */
            padding: 3px;
            border-radius: 5px;
            cursor: pointer;
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

    <!-- Interactive Wooden Bookcase -->
    <div class="container">
        <div class="bookcase" id="bookcase">
            <div class="shelf" id="shelf1"></div>
            <div class="shelf" id="shelf2"></div>
            <div class="shelf" id="shelf3"></div>
            <div class="shelf" id="shelf4"></div>
        </div>
    </div>

    <!-- Add Book Button -->
    <button class="btn btn-success add-book-btn" data-bs-toggle="modal" data-bs-target="#customizeBookModal">Add Book</button>
    <button class="btn btn-success add-friend-btn" onclick="openFriendModal()">Friends</button>

    <!-- Modal for Adding a Book -->
    <div class="modal fade" id="customizeBookModal" tabindex="-1" aria-labelledby="customizeBookModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customizeBookModalLabel">Customize Your Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bookCustomizationForm" action="addBook.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="bookColor" class="form-label">Choose Book Color</label>
                            <input type="color" class="form-control" id="bookColor" name="bookColor" value="#3498db">
                        </div>
                        <div class="mb-3">
                            <label for="bookName" class="form-label">Enter Book Title</label>
                            <input type="text" class="form-control" id="bookName" name="bookName" placeholder="Book Title">
                        </div>
                        <div class="mb-3">
                            <label for="bookCover" class="form-label">Upload Custom Book Cover</label>
                            <input type="file" class="form-control" id="bookCover" name="bookCover" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="bookWidth" class="form-label">Set Book Width (Max: 100px)</label>
                            <input type="number" class="form-control" id="bookWidth" name="bookWidth" min="10" max="100" placeholder="e.g., 50">
                        </div>
                        <div class="mb-3">
                            <label for="bookHeight" class="form-label">Set Book Height (Max: 100px)</label>
                            <input type="number" class="form-control" id="bookHeight" name="bookHeight" min="10" max="100" placeholder="e.g., 100">
                        </div>
                        <div class="mb-3">
                            <label for="shelfSelection" class="form-label">Select Shelf</label>
                            <select class="form-control" id="shelfSelection" name="shelfID">
                                <option value="1">Shelf 1</option>
                                <option value="2">Shelf 2</option>
                                <option value="3">Shelf 3</option>
                                <option value="4">Shelf 4</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="addCustomBook(event)" data-bs-dismiss="modal">Add Book</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing a Book -->
    <div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBookModalLabel">Edit Book</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bookCustomizationForm">
                            <div class="mb-3">
                                <label for="editBookColor" class="form-label">Book Color</label>
                                <input type="color" class="form-control" id="editBookColor" name="bookColor" value="#3498db">
                            </div>
                            <div class="mb-3">
                                <label for="editBookName" class="form-label">Book Title</label>
                                <input type="text" class="form-control" id="editBookName">
                            </div>
                            <div class="mb-3">
                                <label for="editBookImage" class="form-label"> Current Book Cover</label>
                                <input type="file" class="form-control" id="editBookImage" name="bookCover" accept="image/*">
                                <img id="currentBookImagePreview" src="" alt="" style="max-width: 50%; margin-top: 10px;">
                                <button type="button" class="btn btn-warning mt-2" id="deleteImageBtn">Delete Image</button>
                            </div>
                            <div class="mb-3">
                                <label for="editBookWidth" class="form-label">Set Book Width (Max: 100px)</label>
                                <input type="number" id="editBookWidth" name="bookWidth" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="editBookHeight" class="form-label">Set Book Height (Max: 100px)</label>
                                <input type="number" id="editBookHeight" name="bookHeight" class="form-control">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="deleteBook()">Delete Book</button>
                        <button type="button" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let bookId = 0;
        let currentBookID = null;

        //Loads and displays all the books each time the page is laoded
        function loadBooks() {
            const bookcase = document.getElementById('bookcase');
            const shelves = bookcase.getElementsByClassName('shelf');
            for (let shelf of shelves) {
                shelf.innerHTML = ''; //Clear the contents of each shelf
            }
            fetch('loadBooks.php')
                .then(response => response.json())
                .then(books => {
                    console.log(books);
                    if (Array.isArray(books) && books.length > 0) {
                        books.sort((a, b) => a.bookOrder - b.bookOrder);
                        books.forEach(book => {
                            //Create a new book element
                            const bookElement = document.createElement('div');
                            bookElement.classList.add('book');
                            bookElement.setAttribute('draggable', 'true');
                            bookElement.setAttribute('id', 'book-' + book.bookID); //Ensure each book has a unique ID

                            const titleElement = document.createElement('span');
                            titleElement.classList.add('book-title');
                            titleElement.innerText = book.bookName;
                            bookElement.appendChild(titleElement);

                            //Apply dimensions
                            bookElement.style.height = book.bookHeight ? `${book.bookHeight}px` : '100';
                            bookElement.style.width = book.bookWidth ? `${book.bookWidth}px` : '50';

                            adjustTextSize(bookElement); 

                            //Check if there's a cover image or color
                            if (book.imagePath){
                                console.log(`Image Path: ${book.imagePath}`);
                                bookElement.style.backgroundImage = `url(${book.imagePath})`;
                                bookElement.style.backgroundColor = 'transparent';
                                bookElement.style.backgroundSize = 'cover';
                            }
                            else {
                                bookElement.style.backgroundColor = book.bookColor || '#3498db'; // Default color
                                bookElement.style.backgroundImage = 'none';
                            }

                            //Set the title text color (e.g., white for readability)
                            bookElement.style.color = 'white';

                            //Find the correct shelf based on the shelfID from the database
                            const shelfId = `shelf${book.shelfID || 1}`; // Default to shelf 1 if shelfID is missing
                            const shelfElement = document.getElementById(shelfId);
                            
                            if (shelfElement) {
                                shelfElement.appendChild(bookElement);
                            } else {
                                console.error(`Shelf ID ${shelfId} not found`);
                            }

                            //Add double-click event listener to open the edit modal
                            bookElement.addEventListener('dblclick', () => {
                                openEditModal(book); // Pass the entire book object to openEditModal
                            });

                            //Add drag event listeners
                            bookElement.addEventListener('dragstart', dragStart);
                            bookElement.addEventListener('dragend', dragEnd);
                        });
                    } else {
                        console.log('No books to load.');
                    }
                })
                .catch(error => console.error('Failed to load books:', error));
        }
        
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
        
        //Add book functionality
        function addCustomBook() {
            event.preventDefault();
            
            const color = document.getElementById('bookColor').value;
            const imageFile = document.getElementById('bookCover').files[0];
            const bookName = document.getElementById('bookName').value || 'Untitled';
            const shelfID = document.getElementById('shelfSelection').value;
            const bookWidth = document.getElementById('bookWidth').value || 50;
            const bookHeight = document.getElementById('bookHeight').value || 100;
            const newBookOrder = document.getElementById(`shelf${shelfID}`).children.length + 1;

            //Validate dimensions
            if (bookWidth < 10 || bookWidth > 100 || bookHeight < 10 || bookHeight > 100) {
                alert("Book dimensions must be between 10 and 100 pixels.");
                return;
            }

            //Create a new book element immediately on the front-end
            const book = document.createElement('div');
            book.classList.add('book');
            book.setAttribute('draggable', 'true');
            book.setAttribute('id', 'book-' + bookId);
            book.style.width = `${bookWidth}px`;
            book.style.height = `${bookHeight}px`;
            book.style.backgroundColor = color;

            //Set the book title on the spine
            book.innerText = bookName;
            book.style.writingMode = 'vertical-rl'; //Vertical text
            book.style.transform = 'rotate(180deg)'; //Rotate to match book spine direction
            book.style.color = 'white'; //Title text color

            //Check if there is an uploaded image
            if (imageFile) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    book.style.backgroundImage = `url(${e.target.result})`;
                    book.style.backgroundSize = 'cover';
                };
                reader.readAsDataURL(imageFile);
            } else {
                book.style.backgroundColor = color;
            }

            //Add drag and drop functionality
            book.addEventListener('dragstart', dragStart);
            book.addEventListener('dragend', dragEnd);

            //Add the book to the correct shelf
            document.getElementById(`shelf${shelfID}`).appendChild(book);
            bookId++;

            //Send the data to the server
            const formData = new FormData();
            formData.append('bookName', bookName);
            formData.append('bookColor', color);
            formData.append('shelfID', shelfID);
            formData.append('bookWidth', bookWidth);
            formData.append('bookHeight', bookHeight);
            formData.append('bookOrder', newBookOrder);
            if (imageFile) {
                formData.append('bookCover', imageFile);
            }

            fetch('addBook.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Book added successfully');
                    //Reset the form
                    document.getElementById('bookCustomizationForm').reset();
                    //Hide the modal
                    var modal = bootstrap.Modal.getInstance(customizeBookModal);
                    modal.hide(); //Hide the modal
                    loadBooks();
                } else {
                    console.error('Failed to add book:', data.message);
                    alert(data.message); //Show validation message to user
                }
            })
            .catch(error => console.error('Error adding book:', error));
        }

        //Listen for when the modal is shown
        var customizeBookModal = document.getElementById('customizeBookModal');
        customizeBookModal.addEventListener('show.bs.modal', function () {
            //Reset the form fields every time the modal is opened
            document.getElementById('bookCustomizationForm').reset();
        });

        //Updates the shelf in the database for books when they are moved around
        function updateShelfInDatabase(bookID, newShelfID, newBookOrder) {
            fetch('updateShelf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ bookID: bookID, shelfID: newShelfID, bookOrder: newBookOrder })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Shelf updated successfully');
                    //loadBooks();
                } else {
                    console.error('Failed to update shelf:', data.error);
                }
            })
            .catch(error => console.error('Error updating shelf:', error));
        }

        function dragStart(event) {
            const bookElement = event.target;
            const oldShelfID = bookElement.closest('.shelf').id.replace('shelf', '');
            bookElement.dataset.oldShelfID = oldShelfID;
            event.dataTransfer.setData('bookID', bookElement.id);
        }


        function dragEnd(event) {
            event.target.classList.remove('draggable');
        }

        document.querySelectorAll('.shelf').forEach(shelf => {
            shelf.addEventListener('dragover', dragOver);
            shelf.addEventListener('drop', drop);
        });

        function dragOver(event) {
            event.preventDefault();
        }

        function drop(event) {
            event.preventDefault();

            const bookID = event.dataTransfer.getData('bookID');
            const bookElement = document.getElementById(bookID);
            const targetShelfID = event.target.closest('.shelf').id; //Closest shelf
            const targetShelf = document.getElementById(targetShelfID);

            //Extract numeric shelf ID
            const newShelfID = targetShelfID.replace('shelf', '');

            //Insert the book at the correct position
            const dropTarget = event.target.closest('.book');
            if (dropTarget) {
                targetShelf.insertBefore(bookElement, dropTarget);
            } else {
                targetShelf.appendChild(bookElement);
            }

            //Get current order of books on the new shelf
            const shelfBooks = Array.from(targetShelf.children);

            //Update book order for all books in the new shelf
            shelfBooks.forEach((element, index) => {
                const currentBookID = element.id.replace('book-', '');
                const newOrder = index + 1; //Ensure unique sequential order
                console.log(`Updating: BookID = ${currentBookID}, ShelfID = ${newShelfID}, BookOrder = ${newOrder}`);
                updateShelfInDatabase(currentBookID, newShelfID, newOrder);
            });

            //Handle old shelf if the book was moved
            const oldShelfID = bookElement.dataset.oldShelfID;
            if (oldShelfID && oldShelfID !== newShelfID) {
                const oldShelf = document.getElementById(`shelf${oldShelfID}`);
                const oldShelfBooks = Array.from(oldShelf.children);

                //Update book order for all books in the old shelf
                oldShelfBooks.forEach((element, index) => {
                    const currentBookID = element.id.replace('book-', '');
                    const newOrder = index + 1; // Adjust orders in the old shelf
                    updateShelfInDatabase(currentBookID, oldShelfID, newOrder);
                });
            }

            //Update the old shelf ID for the moved book
            bookElement.dataset.oldShelfID = newShelfID;
        }

        //Load books from the server when the page loads
        window.onload = function() {
            loadBooks();
        };

        //Function to edit book details
        function openEditModal(book) {
            console.log("Opening edit modal for book:", book); //Debugging
            currentBookID = book.bookID;
            //Fetch book details using the bookID
            fetch(`getBookDetails.php?bookID=${book.bookID}`)
                .then(response => response.json())
                .then(data => {
                    //Populate modal form with data
                    document.getElementById('editBookName').value = data.bookName;
                    document.getElementById('editBookColor').value = data.bookColor;
                    document.getElementById('editBookHeight').value = data.bookHeight;
                    document.getElementById('editBookWidth').value = data.bookWidth;
                    document.getElementById('editBookImage').files[0] = data.imagePath;

                    //Preview current image (optional)
                    const imagePreview = document.getElementById('currentBookImagePreview');
                    imagePreview.src = data.imagePath ? data.imagePath : '';

                    //Show the edit modal after populating the form
                    const editModal = new bootstrap.Modal(document.getElementById('editBookModal'));
                    editModal.show();
                    console.log("Modal opened");
                })
                .catch(error => {
                    console.error('Error fetching book details:', error);
                });
        }

        let isImageDeleted = false; 
        document.getElementById('deleteImageBtn').addEventListener('click', function () {
            //Clear the image preview and mark the image for deletion
            document.getElementById('currentBookImagePreview').src = "";
            document.getElementById('editBookImage').value = ''; // Clear file input
            isImageDeleted = true; // Flag to send to the backend
            console.log("Image marked for deletion.");
        });

        //Save changes function to update the book in the database
        document.getElementById('saveChangesBtn').addEventListener('click', function() {
            //Collect updated data from the edit form
            const bookID = currentBookID; // Assuming bookID is available in scope
            const updatedName = document.getElementById('editBookName').value;
            const updatedColor = document.getElementById('editBookColor').value;
            const updatedHeight = document.getElementById('editBookHeight').value;
            const updatedWidth = document.getElementById('editBookWidth').value;
            const updatedImage = document.getElementById('editBookImage').files[0];


            //Construct the data to send to the server
            const formData = new FormData();
            formData.append('bookID', bookID);
            formData.append('bookName', updatedName);
            formData.append('bookColor', updatedColor);
            formData.append('bookHeight', updatedHeight);
            formData.append('bookWidth', updatedWidth);
            formData.append('deleteImage', isImageDeleted ? 'true' : 'false');

            if (!isImageDeleted && updatedImage) {
                formData.append('bookCover', updatedImage);
            }
            
            //Send update request to editBook.php
            fetch('editBook.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Book updated successfully.");
                    //Close the modal
                    const editModal = bootstrap.Modal.getInstance(document.getElementById('editBookModal'));
                    editModal.hide();
                    //Reload books to reflect changes
                    loadBooks();
                } else {
                    console.error("Failed to update book:", data.error);
                    alert("Failed to update book.");
                }
            })
            .catch(error => {
                console.error('Error updating book:', error);
            });
        });

        document.getElementById('editBookModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('currentBookImagePreview').src = ""; // Clear image preview
            isImageDeleted = false;
        });

        //Function to delete a book
        function deleteBook() {
            const bookID = currentBookID;

            if (confirm("Are you sure you want to delete this book?")) {
                fetch('deleteBook.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ bookID: bookID })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Book deleted successfully');
                        var modal = bootstrap.Modal.getInstance(document.getElementById('editBookModal')); // Get the modal instance
                        modal.hide(); //Hide the modal
                        loadBooks(); //Reload the bookshelf to reflect the deleted book
                    } else {
                        console.error('Failed to delete book:', data.error);
                        alert("Failed to delete book: " + data.error);
                    }
                })
                .catch(error => console.error('Error deleting book:', error));
            }
        }
    </script>
</body>
</html>
