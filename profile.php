<?php
  session_start();
  include 'db_connect.php';

  // Redirect to the login page if the user is not logged in
  if (!isset($_SESSION['email'])) {
    header("Location: logout.php");
    exit();
  }

  // Determine redirection page based on email domain
  $redirectPage = 'userHome.php';  // default
  if ($_SESSION['admin'] === 1) {
      $redirectPage = 'adminHome.php';
  }

  // Check if the session variables are set
  if (isset($_SESSION['user_id']) && isset($_SESSION['firstName']) && isset($_SESSION['lastName']) && isset($_SESSION['email'])) {
    $userID = $_SESSION['user_id'];
    $firstName = $_SESSION['firstName'];
    $lastName = $_SESSION['lastName'];
    $email = $_SESSION['email'];
  } else {
    // If the session variables are not set, redirect to the login page or handle the error
    header("Location: logout.php");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
          width: 100%;
          height: 100vh;
          background-size: cover;
          background-position: center;
      }
      body.user {
          background-image: url('images/bkg7.jpeg'); /* Background for regular users */
      }
      body.admin {
          background-color: #f0f0f0; /* Neutral background for admin */
      }
      .heading {
          /*max-width: fit-content;*/
          /*margin: 20px;*/
          margin-left: 0px; 
          padding: 10px 0;
          border-left: 5px;
          border-right: 5px;
          display: flex; /* Use flexbox */
          align-items: center;
          width: 100%;
      }
      .heading-content {
            display: flex;
            align-items: center;
            width: 100%;
            margin-left: 20px;
        }
      /*
      .profile {
        position: relative;
        max-width: 600px;
        margin: 50px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 20px rgba(0, 0, 0, 0.4);
        text-align: center;
      }
      */
      p {
        color: #666;
        margin-bottom: 10px;
      }
      .container {
        position: relative;
        max-width: 600px;
      }
      .profile-modal {
            margin-top: 50px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        .profile-modal h2 {
            font-size: 35px;
            margin-bottom: 15px;
        }
        .profile-modal .user-info {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 0px;
        }
        .profile-modal .user-info i {
            margin-right: 10px;
            margin-bottom: 9px;
        }
      .btn-close {
          position: absolute;
          top: 10px;
          right: 20px;
          background-color: transparent;
          border: none;
          font-size: 20px;
          cursor: pointer;
      }
      /* Navbar */
      .navbar {
          padding: 10px;
      }
      /* Aligning Profile and Logout buttons to the right */
      .navbar-buttons {
          display: flex;
          justify-content: flex-end;
          width: 100%;
      }
      .deleteAccount-btn {
        padding: 10px 15px;
        background-color: #d63a3a;
        color: #fff;
        border: none;
        /*border-radius: 30px;*/
        cursor: pointer;
        margin-left: 5px;
        margin-top: 15px;
        font-size: 15px;
        transition: background-color 0.3s ease;
      }
      .deleteAccount-btn:hover {
        background-color: #a92618;
      }
      .deleteAccount-btn::after {
        content: 'WARNING: This deletes user account and books.';
        position: absolute;
        bottom: 15%; 
        right: 1%;
        transform: translateX(-50%);
        padding: 3px 6px; /* Adjusted padding */
        background-color: rgba(87, 81, 81, 0.99); 
        color: #ffffff;
        font-size: 12px;
        /*font-family: 'Charter';*/
        /*border-radius: 3px;  Smaller border radius */
        white-space: nowrap; /* Prevent text from wrapping */
        opacity: 0; /* Hide by default */
        pointer-events: none; /* Ensure the tooltip doesn't interfere with button clicks */
        transition: opacity 0.3s ease;
    }
    .deleteAccount-btn:hover::after {
        opacity: 1;
    }
    .heading-image {
      height: 70px; /* Adjust the height of the image */
      width: auto; /* Let the width adjust proportionally */
      margin-left: 5px;
    }
    </style>
  </head>
  <body class="<?php echo $_SESSION['admin'] === 1 ? 'admin' : 'user'; ?>">
  <div class="container-fluid">
        <div class="row align-items-center mt-2">
            <div class="col-6">
                <img src="images/ShelveIt-01.png" alt="Image" class="heading-image">
                <!--<h1>ShelveIt!</h1>-->
            </div>
            <div class="col-6 d-flex justify-content-end">
                <button class="btn btn-primary" onclick="window.location.href='logout.php';">Logout</button>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Profile Modal like Layout -->
        <div class="profile-modal">
            <button type="button" class="btn-close" onclick="window.location.href='<?php echo $redirectPage; ?>';"></button>
            <h2>User Profile</h2>
            <div class="user-info">
                <i class="fas fa-user-shield"></i>
                <p>Account Number: <?php echo htmlspecialchars($userID); ?></p>
            </div>
            <div class="user-info">
                <i class="fas fa-user-check"></i>
                <p>First Name: <?php echo htmlspecialchars($firstName); ?></p>
            </div>
            <div class="user-info">
                <i class="fas fa-signature"></i>
                <p>Last Name: <?php echo htmlspecialchars($lastName); ?></p>
            </div>
            <div class="user-info">
                <i class="fas fa-at"></i>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
            </div>
            <button class="btn btn-danger deleteAccount-btn" onclick="confirmDelete()">Delete Account</button>
        </div>
    </div>
    <!-- JavaScript for confirmation dialog and form submission -->
    <script>
        function confirmDelete() {
            if (confirm("Are you sure you want to delete your account? This action cannot be undone.")) {
                document.getElementById("deleteForm").submit();
            }
        }
    </script>
    <!-- Form for account deletion -->
    <form id="deleteForm" action="deleteAccount.php" method="post" style="display: none;">
        <input type="hidden" name="confirm_delete" value="yes">
    </form>
  </body>
</html>
