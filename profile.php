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
          background-color: #f0f0f0;
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
      .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #e2dfdf;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        z-index: 1; /* Ensure it's above other content */
      }
      .heading-content {
          display: flex;
          align-items: center;
          width: 100%;
          margin-left: 20px;
      }
      h2 {
          font-size: 35px;
          margin-right: 15px;
      }
      .heading-content h2 {
          position: relative; /* Ensure relative positioning */
          transition: transform 0.2s ease; /* Add transition effect */
      }
      .heading-content h2:hover {
          transform: translateY(-5px); /* Move the heading up slightly on hover */
      }
      .heading-content h2::after {
          content: 'Home'; /* Display "Home" text for the tooltip */
          position: absolute;
          top: calc(100% + 5px); /* Position the tooltip below the heading */
          left: 50%;
          transform: translateX(-50%);
          padding: 3px 6px; /* Add padding to the tooltip */
          background-color: rgba(255, 254, 254, 0.8); /* Set background color for the tooltip */
          color: #000000; /* Set text color for the tooltip */
          font-size: 12px; /* Set font size for the tooltip */
          border-radius: 3px; /* Add border-radius for rounded corners */
          white-space: nowrap; /* Prevent text from wrapping */
          opacity: 0; /* Initially hide the tooltip */
          transition: opacity 0.2s ease; /* Add transition effect */
      }
      .heading-content h2:hover::after {
          opacity: 1; /* Show the tooltip on hover */
      }
      .heading-content h2::before {
          content: ''; /* Create a pseudo-element for the underline */
          position: absolute;
          left: 0;
          bottom: -3px; /* Adjust the distance of the underline from the text */
          width: 0; /* Initially set the width to 0 */
          height: 2px; /* Set the height of the underline */
          background-color: #ff0000dd; /* Set the color of the underline */
          opacity: 0; /* Initially hide the underline */
          transition: width 0.3s ease, opacity 0.3s ease; /* Add transition effect */
      }
      .heading-content h2:hover::before {
          width: 100%; /* Expand the width to 100% on hover */
          opacity: 1; /* Show the underline on hover */
      }
      p {
        color: #666;
        margin-bottom: 10px;
      }
      .user-info {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0px;
      }
      .user-info i {
        margin-right: 10px;
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

        .navbar-buttons button {
            margin-right: 10px;
            margin-top: 10px;
        }
        /*
      .logout-btn {
        padding: 10px 15px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        margin-left: 5px;
        font-size: 15px;
        font-family: "Charter";
        transition: background-color 0.3s ease;
      }
      .logout-btn:hover {
        background-color: #0056b3;
      }
      */
      .deleteAccount-btn {
        padding: 10px 15px;
        background-color: #d63a3a;
        color: #fff;
        border: none;
        border-radius: 30px;
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
       /* font-family: 'Charter';*/
        border-radius: 3px; /* Smaller border radius */
        white-space: nowrap; /* Prevent text from wrapping */
        opacity: 0; /* Hide by default */
        pointer-events: none; /* Ensure the tooltip doesn't interfere with button clicks */
        transition: opacity 0.3s ease;
    }
    .deleteAccount-btn:hover::after {
        opacity: 1;
    }
    </style>
  </head>
  <body>
    <div class="container-fluid">
        <div class="row align-items-center mt-2">
            <div class="col-6">
                <h1>ShelveIt!</h1>
            </div>
            <div class="col-6 d-flex justify-content-end">
                <button class="btn btn-primary" onclick="window.location.href='logout.php';">Logout</button>
            </div>
        </div>
    </div>
    <div class="profile">
      <button id="closePopup" class="close-btn" onclick="window.location.href='<?php echo $redirectPage; ?>';">X</button>
      <h1>User Profile</h1>
      <div class="user-info">
        <i class="fas fa-user-shield"></i>
        <p>Account Number: <?php echo htmlspecialchars($userID); ?></p>
      </div>
      <div class="user-info">
        <i class="fas fa-signature"></i>
        <p>First name: <?php echo htmlspecialchars($firstName); ?></p>
      </div>
      <div class="user-info">
        <i class="fas fa-signature"></i>
        <p>Last name: <?php echo htmlspecialchars($lastName); ?></p>
      </div>
      <div class="user-info">
        <i class="fas fa-at"></i>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>
      </div>
      <button class="deleteAccount-btn" onclick="confirmDelete()">
          Delete Account
      </button>
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