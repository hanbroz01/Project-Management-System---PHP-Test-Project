<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Only let authenticated HR Managers/Admins access this page
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$errors = [];

// Database connection details
$host = 'mariadb';
$db   = 'db_data_test';
$user = 'user';
$pass = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Security Check: Make sure the user actually used POST to get here
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Check each field using empty() and trim()
    if (empty(trim($_POST['first_name']))) {
        $errors[] = "First Name is required.";
    }

    if (empty(trim($_POST['last_name']))) {
        $errors[] = "Last Name is required.";
    }

    if (empty(trim($_POST['email']))) {
        $errors[] = "Email Address is required.";
    }

    if (empty(trim($_POST['role'] ?? ''))) {
        $errors[] = "User Role is required.";
    }

    // DUPLICATE CHECK: Check if email already exists in the database
    if (!empty(trim($_POST['email']))) {
        $submitted_email = strtolower(trim($_POST['email']));

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$submitted_email]);
        if ($stmt->fetch()) {
            $errors[] = "A user with this email address already exists.";
        }
    }

    if (!empty($errors)) {
        $errorMessage = "Please fix the following errors:\\n";
        foreach ($errors as $error) {
            $errorMessage .= "- " . $error . "\\n";
        }
        echo "<script>alert('$errorMessage');</script>";
    } else {
        // 1. GENERATE USERNAME: Last name + random 4-digit number (e.g., smith3921)
        $clean_last_name = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(trim($_POST['last_name'])));
        if (empty($clean_last_name)) {
            $clean_last_name = 'user';
        }

        // Ensure uniqueness by checking the database loop
        do {
            $random_number = rand(1000, 9999);
            $username = $clean_last_name . $random_number;

            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $check_stmt->execute([$username]);
            $username_exists = $check_stmt->fetch();
        } while ($username_exists);

        // 2. DEFAULT SECURITY ENCRYPTION: Lock account to universal setup password ("password")
        $default_raw_password = "password";
        $secure_hash = password_hash($default_raw_password, PASSWORD_BCRYPT);

        // 3. MAP COMPANY LEVEL: Admin if role is Admin or Manager, else Staff
        $selected_role = $_POST['role'];
        $company_level = ($selected_role === 'Admin' || $selected_role === 'Manager') ? 'Admin' : 'Staff';

        // 4. INSERT INTO DATABASE
        $sql = "INSERT INTO users (username, password, first_name, last_name, email, company_level, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Active')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $username,
            $secure_hash,
            trim($_POST['first_name']),
            trim($_POST['last_name']),
            strtolower(trim($_POST['email'])),
            $company_level
        ]);

        $_SESSION['flash_message'] = "Employee Added: " . trim($_POST['first_name']) . " " . trim($_POST['last_name']) . " (Username: $username - Role: $company_level)";

        header("Location: view_employee.php");
        exit();
    }
}

$page_title = "Create Employee Profile";
include __DIR__ . '/../templates/header_template.php';
?>
<div class="dashboard-wrapper">
    <h2>Create New Employee Profile</h2>

    <a href="../index.php" class="btn">Back to Dashboard</a>

    <hr />

    <div class="create_menu">
        <form action="create_employee.php" method="POST">

            <label>First Name:</label><br>
            <input type="text" name="first_name" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" required><br><br>

            <label>Email Address:</label><br>
            <input type="email" name="email" required placeholder="name@company.com"><br><br>

            <label>Employee Role:</label><br>
            <select name="role" id="role" required>
                <option value="" disabled selected>-- Select a Role --</option>
                <option value="Admin">Admin</option>
                <option value="Manager">Manager</option>
                <option value="Staff">Staff</option>
                <option value="Volunteer">Volunteer</option>
            </select>
            <br><br>
            <button type="submit" class="submit-btn">Create New Employee Profile</button>
        </form>
    </div>
</div>
<?php include '../templates/footer_template.php'; ?>