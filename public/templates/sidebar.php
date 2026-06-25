<?php
// Start Session automatically in the header 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Automatically detect if the page is in a subfolder of the root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$base_path = (basename(dirname($_SERVER['PHP_SELF'])) == 'templates' || basename(dirname($_SERVER['PHP_SELF'])) == 'schedule' || basename(dirname($_SERVER['PHP_SELF'])) == 'employee-profiles') ? '../' : '';
?>

<aside class="sidebar">

    <div class="sidebar-brand">
        <div class="brand-logo-wrapper">
            <a href="<?php echo $base_path; ?>index.php" class="brand-logo-link">
                <img src="<?php echo $base_path; ?>css/images/logo.png" alt="HR Core Logo" class="sidebar-logo-img">
            </a>
        </div>
        <div class="brand-text">
            <h2>HR CORE</h2>
            <span>Portals & Solutions</span>
        </div>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li class="menu-item active">
                <a href="dashboard.php">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="employees.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Employees</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="schedule.php">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Scheduling</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="requests.php">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span>Requests</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="payroll.php">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Payroll</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-avatar">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="user-info">
            <span class="username">Han Static</span>
            <span class="user-role">Manager Static</span>
        </div>
    </div>

</aside>