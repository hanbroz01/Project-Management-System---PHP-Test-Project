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

    // Database connection details
    $host = 'mariadb';            // Service name in docker-compose.yml
    $db   = 'db_data_test';  // Your database name
    $user = 'user';          // Database user
    $pass = 'password';      // Database password

    try {
        // Connect using PDO
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user_found = $stmt->fetch();

        // Verify password and user status
        if ($user_found && password_verify($password, $user_found['password'])) {

            // Check if status is Active
            if ($user_found['status'] !== 'Active') {
                $error_message = "This account is inactive. Please contact an administrator.";
            } else {
                session_regenerate_id(true);

                // Set session variables to match your dashboard expectations
                $_SESSION['user_id'] = $user_found['id'];
                $_SESSION['email'] = $user_found['email'];
                $_SESSION['first_name'] = $user_found['first_name'];
                $_SESSION['last_name'] = $user_found['last_name'];
                $_SESSION['role'] = $user_found['company_level'] ?? 'Employee';
                $_SESSION['access_level'] = $user_found['company_level'] ?? 'Employee';

                header("Location: index.php");
                exit();
            }
        } else {
            $error_message = "Invalid email address or password.";
        }
    } catch (PDOException $e) {
        echo "DEBUG ERROR: " . $e->getMessage();
        exit();
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