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

<h1>Welcome to your Dashboard, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
<p>Account Type: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>

  <hr />
  <div class="nav-main">
    <a href="employee-profiles/create_employee.php" class="nav-link">
      <button class="nav-btn">Create New Employee Profile</button>
    </a>

    <a href="employee-profiles/view_employee.php" class="nav-link">
      <button class="nav-btn">View Employee Profiles</button>
    </a>

        <a href="schedule/schedule.php" class="nav-link">
      <button class="nav-btn">View Employee Schedules</button>
    </a>

  </div>
</div>
  <?php include 'templates/footer_template.php'; ?>