<?php
// Start the session to manage login states across pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Verify that the user is logged in and has Admin privileges
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// Define database connection credentials for MariaDB
$host = 'mariadb';
$db   = 'db_data_test';
$user = 'user';
$pass = 'password';

try {
    // Attempt to establish a secure database connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Stop execution if the database connection fails and show the error
    die("Database connection failed: " . $e->getMessage());
}

// Initialize variables for holding user data and validation errors
$user_to_edit = null;
$errors = [];

// Handle incoming GET requests to load employee data when arriving from view_employee.php
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_to_find = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    // Fetch the specific user record from the database by their unique ID
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, company_level FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id_to_find]);
    $user_to_edit = $stmt->fetch();

    // If no user is found with that ID, set a flash message and redirect back
    if (!$user_to_edit) {
        $_SESSION['flash_message'] = "Error: User could not be found in the database.";
        header("Location: view_employee.php");
        exit();
    }
}

// Handle form submission via POST when the user clicks "Save Changes"
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']);
    $new_role = $_POST['role'] ?? 'Staff';

    // Validate that required name fields are not left blank
    if (empty($new_first_name)) {
        $errors[] = "First name cannot be empty.";
    }
    if (empty($new_last_name)) {
        $errors[] = "Last name cannot be empty.";
    }

    // If no validation errors occurred, proceed with updating the database record
    if (empty($errors)) {
        // Directly save the selected role into the company_level column
        $update_stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, company_level = ? WHERE id = ?");
        $update_stmt->execute([
            $new_first_name,
            $new_last_name,
            $new_role, // Uses the exact selected dropdown value (Admin, Manager, Staff, Volunteer)
            $user_id
        ]);

        // Set success notification and redirect back to the employee list
        $_SESSION['flash_message'] = "Successfully edited Employee data for " . htmlspecialchars($new_first_name . ' ' . $new_last_name) . "!";
        header("Location: view_employee.php");
        exit();
    }

    // If errors exist, trigger a JavaScript alert and return to the previous page
    if (!empty($errors)) {
        $errorMessage = implode("\\n", $errors);
        echo "<script>alert('$errorMessage'); window.history.back();</script>";
        exit();
    }
}

// Set page title and include standard layout templates
$page_title = "Edit Employee";
include __DIR__ . '/../templates/header_template.php';
?>

<div class="dashboard-wrapper">
    <h2>Edit Employee Data</h2>

    <a href="../index.php" class="btn">Back to Dashboard</a>
    <a href="view_employee.php" class="btn">Back to View all Employees</a>
    <hr />
    <br>

    <div class="form-container">
        <!-- Form submits updated values back to this script via POST -->
        <form action="edit_employee.php" method="POST">

            <!-- Hidden input to safely track which user ID is being edited -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_to_edit['id'] ?? ''); ?>">

            <label><strong>Email Address (Cannot Be Changed):</strong></label>
            <p><?php echo htmlspecialchars($user_to_edit['email'] ?? ''); ?></p>
            <br>

            <label>First Name:</label><br>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user_to_edit['first_name'] ?? ''); ?>" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user_to_edit['last_name'] ?? ''); ?>" required><br><br>

            <label>Employee Role:</label><br>
            <select name="role" required>
                <option value="Admin" <?php echo (($user_to_edit['company_level'] ?? '') === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="Manager" <?php echo (($user_to_edit['company_level'] ?? '') === 'Manager') ? 'selected' : ''; ?>>Manager</option>
                <option value="Staff" <?php echo (($user_to_edit['company_level'] ?? '') === 'Staff') ? 'selected' : ''; ?>>Staff</option>
                <option value="Volunteer" <?php echo (($user_to_edit['company_level'] ?? '') === 'Volunteer') ? 'selected' : ''; ?>>Volunteer</option>
            </select>
            <br><br>

            <button type="submit" class="btn-save">Save Changes</button>
            <a href="view_employee.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</div>

<?php include '../templates/footer_template.php'; ?>