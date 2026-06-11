<?php
// Start Session automatically in the header 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Automatically detect if the page is in a subfolder of the root
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
</nav>

    <div class="dashboard-wrapper">