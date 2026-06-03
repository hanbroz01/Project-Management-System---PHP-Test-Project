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
  <div class="nav-main">
    <a href="create_employee.php" class="nav-link">
      <button class="nav-btn">Create New Employee Profile</button>
    </a>

    <a href="schedule.php" class="nav-link">
      <button class="nav-btn">View Employee Schedules</button>
    </a>

    <a href="view_user.php" class="nav-link">
      <button class="nav-btn">View Employees</button>
    </a>
  </div>
<?php if (isset($_SESSION['flash_message'])): ?>
        <div id="flash-data" data-message="<?php echo htmlspecialchars($_SESSION['flash_message']); ?>"></div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            const flashElement = $('#flash-data');
            
            if (flashElement.length) {
                const message = flashElement.data('message');
                
                // Configure global Toastr settings professionally
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right", /* Changed to the reliable default standard */
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                
                // Fire a clean success notification
                toastr.success(message, 'System Notification');
            }
        });
    </script>
  </body>
</html>