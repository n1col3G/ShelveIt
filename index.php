<?php 
    session_start();
    include 'db_connect.php';

    // Database connection
    $conn = Database::dbConnect();

    if (!$conn) {
        die("Failed to connect to the database");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Sign Up - ShelveIt!</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .auth-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .auth-btn {
            width: 100%;
            margin-top: 10px;
        }
        .toggle-link {
            text-align: center;
            margin-top: 10px;
        }
        .auth-form {
            display: none;
        }
        .auth-form.active {
            display: block;
        }
        .heading-image {
            height: 50px; /* Adjust the height of the image */
            width: auto; /* Let the width adjust proportionally */
            margin-left: -9px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h3 class="text-center mb-4" id="auth-title">Login to 
            <img src="images/ShelveIt-01.png" alt="Image" class="heading-image">
        </h3>

        <!-- Login Form -->
        <form id="loginForm" class="auth-form active" action="login_user.php" method="post">
            <div class="form-group">
                <label for="loginEmail" class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" id="loginEmail" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="loginPassword" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary auth-btn">Login</button>
        </form>

        <!-- Sign Up Form -->
        <form id="signupForm" class="auth-form" action="register_user.php" method="post">
            <div class="form-group">
                <label for="signupFirstName" class="form-label">First Name</label>
                <input type="text" name="firstname" class="form-control" id="signupFirstName" placeholder="Enter your first name" required>
            </div>
            <div class="form-group">
                <label for="signupLastName" class="form-label">Last Name</label>
                <input type="text" name="lastname" class="form-control" id="signupLastName" placeholder="Enter your last name" required>
            </div>
            <div class="form-group">
                <label for="signupEmail" class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" id="signupEmail" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="signupPassword" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="signupPassword" placeholder="Create a password" required>
            </div>
            <button type="submit" class="btn btn-primary auth-btn">Sign Up</button>
        </form>

        <!-- Toggle Between Login/Signup -->
        <div class="toggle-link">
            <p id="toggleMessage">Don't have an account? <a href="#" id="toggleForm">Sign up here</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle between forms -->
    <script>
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        const authTitle = document.getElementById('auth-title');
        const toggleMessage = document.getElementById('toggleMessage');
        const toggleFormLink = document.getElementById('toggleForm');
    
        //Function to switch forms between Login and Sign-up
        function switchForms() {
            const imgTag = '<img src="images/ShelveIt-01.png" alt="Image" class="heading-image">';

            if (loginForm.classList.contains('active')) {
                loginForm.classList.remove('active');
                signupForm.classList.add('active');
                authTitle.innerHTML = `Sign Up for ${imgTag}`;
                toggleMessage.innerHTML = 'Already have an account? <a href="#" id="toggleForm">Login here</a>';
            } else {
                signupForm.classList.remove('active');
                loginForm.classList.add('active');
                authTitle.innerHTML = `Login to ${imgTag}`;
                toggleMessage.innerHTML = 'Don\'t have an account? <a href="#" id="toggleForm">Sign up here</a>';
            }
        }
    
        //Add event listener to the toggle link
        document.addEventListener('click', function(event) {
            if (event.target.id === 'toggleForm') {
                event.preventDefault();
                switchForms();
            }
        });
    
        document.getElementById('signupForm').addEventListener('submit', function(event) {
        // Remove event.preventDefault() to allow form submission
        // Optional: Add form validation here if needed before submission
        // The backend (register_user.php) will handle user creation and redirect
        });

    </script>
</body>
</html>
