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
        <div class="nav-brand">
            <a href="<?php echo $base_path; ?>index.php" class="brand-logo-link">
                <img src="<?php echo $base_path; ?>css/images/logo.png" alt="HR Core Logo" class="logo-img">
            </a>

            <div class="brand-text-wrapper">
                <span class="brand-name">HR CORE</span>
                <span class="brand-sub">Portals & Solutions</span>
            </div>
        </div>

        <section class="right-section">
            <!-- Search Input Form -->
            <form class="search-input-container" role="search">
                <img src="<?php echo $base_path; ?>css/images/search.png" alt="search-logo" class="search-img">
                <input class="search-input-field" type="text" placeholder="Search ... " />
            </form>

            <!-- Login Button -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-badge-container">
                    <div class="user-meta">
                        <span class="user-greeting">
                            Hi, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                        </span>
                        <span class="user-role">
                            <?php echo htmlspecialchars($_SESSION['role']); ?>
                        </span>
                    </div>

                    <a href="<?php echo $base_path; ?>logout.php" class="login-btn logout-btn">
                        <img src="<?php echo $base_path; ?>css/images/login.png" alt="logout" class="login-img logout-img">
                        <span class="login-text">Logout</span>
                    </a>
                </div>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>login.php" class="login-btn">
                    <img src="<?php echo $base_path; ?>css/images/login.png" alt="login" class="login-img">
                    <span class="login-text">Login</span>
                </a>
            <?php endif; ?>
        </section>
    </nav>
    <div class="dashboard-wrapper">