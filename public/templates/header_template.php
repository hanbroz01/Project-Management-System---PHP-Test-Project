<?php
// Start Session automatically in the header 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Automatically detect if the page is in a subfolder of the root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$base_path = (basename(dirname($_SERVER['PHP_SELF'])) == 'templates' || basename(dirname($_SERVER['PHP_SELF'])) == 'schedule' || basename(dirname($_SERVER['PHP_SELF'])) == 'employee-profiles') ? '../' : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : "Employee Portal"; ?></title>

    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>

<body>
    <nav class="main-navigation">
        <!-- Current Logged In User Information -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-badge-container">
                <div class="user-meta">
                    <span class="user-greeting">
                        Hi, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
                    </span>
                    <span class="company-level">
                        Company Level: <?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'Employee'; ?>
                    </span>
                </div>
            </div>

            <section class="right-section">
                <!-- Search Input Form -->
                <form class="search-input-container" role="search">
                    <img src="<?php echo $base_path; ?>css/images/search.png" alt="search-logo" class="search-img">
                    <input class="search-input-field" type="text" placeholder="Search ... " />
                </form>

                <!-- Change Password Button -->
                <a href="/employee-profiles/change_password.php" class="login-btn" style="margin-right: 8px;">
                    <span class="login-text">Change Password</span>
                </a>

                <!-- Logout Button -->
                <a href="<?php echo $base_path; ?>logout.php" class="login-btn logout-btn">
                    <img src="<?php echo $base_path; ?>css/images/login.png" alt="logout" class="login-img logout-img">
                    <span class="login-text">Logout</span>
                </a>

            <?php else: ?>
                <a href="<?php echo $base_path; ?>login.php" class="login-btn">
                    <img src="<?php echo $base_path; ?>css/images/login.png" alt="login" class="login-img">
                    <span class="login-text">Login</span>
                </a>
            <?php endif; ?>
            </section>
    </nav>