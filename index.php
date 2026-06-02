<?php session_start(); ?>
<!doctype html>
<html>
  <head>
    <title>Employee Dashboard</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  </head>
  <body>
    <h1>Employee Management Dashboard</h1>
    <p>Welcome! What would you like to do today?</p>

    <hr />

    <a href="account.php">
      <button style="padding: 10px 20px; font-size: 16px">
        Create New User
      </button>
    </a>

    <br /><br />

    <a href="schedule.php">
      <button style="padding: 10px 20px; font-size: 16px">
        View Employee Schedules
      </button>
    </a>

    <br /><br />

    <a href="view_user.php">
      <button style="padding: 10px 20px; font-size: 16px">View Users</button>
    </a>

    <?php
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        echo "<script>
            toastr.info('$msg', 'Success!', {
                timeOut: 8000, 
                progressBar: true,
                positionClass: 'toast-top-center' 
            });
        </script>";
        
        // Clear the message so it doesn't pop up again if they refresh the page!
        unset($_SESSION['flash_message']);
    }
    ?>
  </body>
</html
 