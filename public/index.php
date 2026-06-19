<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// SECURITY GATE: If the user is completely logged out, send them to the login screen immediately
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Choose custom tab title for this specific page
$page_title = "Employee Dashboard";
include __DIR__ . '/templates/header_template.php';
?>
<div class="dash-top">
  <h1>Welcome to your Dashboard, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
  <div class="date-info">
    <img src="css/images/clock.png" alt="Clock Icon" class="clock">
    <span class="current-date"><?php echo date('l, F j, Y'); ?></span>
  </div>
</div>

<hr />
<div class="navigation">

  <div class="nav-main">
    <a href="employees-working.php" class="card-link">
      <div class="card emps-working">
        <img src="css/images/working.png" alt="Working Icon" class="card-icon">
        <h3>Working</h3>
        <span class="card-number">12</span>
      </div>
    </a>

    <a href="employees-holiday.php" class="card-link">
      <div class="card emps-holiday">
        <img src="css/images/holidays.png" alt="Holiday Icon" class="card-icon">
        <h3>On Holiday</h3>
        <span class="card-number">3</span>
      </div>
    </a>

    <a href="pending-requests.php" class="card-link">
      <div class="card emps-pending">
        <img src="css/images/alerts.png" alt="Alerts Icon" class="card-icon">
        <h3>Pending Alerts</h3>
        <span class="card-number">5</span>
      </div>
    </a>
  </div>

  <div class="nav-right">
    <a href="employee-profiles/create_employee.php" class="nav-link">
      <button class="nav-btn">Create New Employee Profile</button>
    </a>

    <a href="employee-profiles/view_employee.php" class="nav-link">
      <button class="nav-btn">View Employee Profiles</button>
    </a>

    <a href="schedule/schedule.php" class="nav-link">
      <button class="nav-btn">View Employee Schedules</button>
    </a>

    <a href="schedule/schedule.php" class="nav-link">
      <button class="nav-btn">Function</button>
    </a>

    <a href="schedule/schedule.php" class="nav-link">
      <button class="nav-btn">Function</button>
    </a>



  </div>
</div>
</div>

<?php include 'templates/footer_template.php'; ?>