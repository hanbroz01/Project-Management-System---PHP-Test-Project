<?php 
// Chopose custome tab title for this specific page
$page_title = "Employee Dashboard"; 
include 'templates/header_template.php'; 
?>

  <h1>Welcome to your Dashboard</h1>
  <p>What would you like to do today?</p>

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