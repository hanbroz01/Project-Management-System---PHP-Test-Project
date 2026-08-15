<?php
// Start the session to manage login states
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Make sure the user is logged in before accessing this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$error_message = "";
$success_message = "";

// Database connection configuration details for Docker MariaDB
$host = 'mariadb';
$db   = 'db_data_test';
$user = 'user';
$pass = 'password';

try {
    // Establish a secure PDO database connection
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Process form submission when POST request is made
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation checks
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } else {
        // Fetch current user's password hash from database using session user_id
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user_record = $stmt->fetch();

        // Verify that the typed current password matches the database hash
        if ($user_record && password_verify($current_password, $user_record['password'])) {
            // Hash the new password securely using BCRYPT
            $new_secure_hash = password_hash($new_password, PASSWORD_BCRYPT);

            // Update database with the new password hash
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->execute([$new_secure_hash, $_SESSION['user_id']]);

            $success_message = "Your password has been successfully updated!";
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}

// Set custom page title and include header (navigating up from subfolder)
$page_title = "Change Password";
include __DIR__ . '/../templates/header_template.php';
?>
<div class="dashboard-wrapper">
    <h2>Change Your Password</h2>

    <!-- Navigation link back to dashboard -->
    <a href="../index.php" class="btn">Back to Dashboard</a>

    <hr />

    <!-- Display error messages if any -->
    <?php if (!empty($error_message)): ?>
        <div class="error-banner" style="color: #ef4444; margin-bottom: 15px;">
            ❌ <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Display success message if password updated -->
    <?php if (!empty($success_message)): ?>
        <div class="success-banner" style="color: #10b981; margin-bottom: 15px;">
            ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div class="create_menu">
        <form action="change_password.php" method="POST">

            <label>Current Password:</label><br>
            <input type="password" name="current_password" required><br><br>

            <label>New Password:</label><br>
            <input type="password" name="new_password" required placeholder="At least 6 characters"><br><br>

            <label>Confirm New Password:</label><br>
            <input type="password" name="confirm_password" required><br><br>

            <button type="submit" class="submit-btn">Update Password</button>
        </form>
    </div>
</div>
</div>
<!-- Include footer template (navigating up from subfolder) -->
<?php include __DIR__ . '/../templates/footer_template.php'; ?>