<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect straight past this page to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    $users_file = realpath(__DIR__ . '/../data/employee_list.json');
    
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);
        $user_found = null;
        
        foreach ($users as $user) {
            if (strcasecmp($user['email'], $email) === 0) {
                $user_found = $user;
                break;
            }
        }
        
        if ($user_found && password_verify($password, $user_found['password'])) {
            session_regenerate_id(true); 
            
            $_SESSION['user_id'] = $user_found['id'];
            $_SESSION['email'] = $user_found['email'];
            $_SESSION['first_name'] = $user_found['first_name'];
            $_SESSION['last_name'] = $user_found['last_name'];
            $_SESSION['role'] = $user_found['role'];
            $_SESSION['access_level'] = $user_found['access_level'];
            
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid email address or password.";
        }
    } else {
        $error_message = "System error: User registry database missing.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Core - Sign In</title>
    <link rel="stylesheet" href="css/pages/login.css">
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <img src="css/images/logo.png" alt="HR Core Logo">
            <p>HR CORE - Portals & Solutions</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-banner">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Corporate Email</label>
                <input type="email" name="email" class="form-control" required placeholder="name@company.com">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
    </div>

</body>
</html>