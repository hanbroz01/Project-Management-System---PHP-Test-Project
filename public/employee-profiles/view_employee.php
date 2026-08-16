<?php
// Start the session to manage login states
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Only let authenticated HR Managers/Admins view this management directory
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

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
    // Terminate script and show error if database connection fails
    die("Database connection failed: " . $e->getMessage());
}

// Choose custom tab title for this specific page
$page_title = "Employee Profiles";
include __DIR__ . '/../templates/header_template.php';
?>
<div class="dashboard-wrapper">
    <h2>View Employee Profiles</h2>

    <a href="../index.php" class="btn">Back to Dashboard</a>
    <a href="create_employee.php" class="btn">Create New Employee Profile</a>
    <hr />

    <?php
    // Flash message banner display if set
    if (isset($_SESSION['flash_message'])) {
        echo '<div style="background-color: #d1fae5; color: #065f46; padding: 10px; margin-bottom: 15px; border-radius: 5px;">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
        unset($_SESSION['flash_message']); // clear it so it doesn't stay forever
    }

    try {
        // Query the database to fetch all users/employees ordered by latest ID
        $stmt = $pdo->query("SELECT id, username, first_name, last_name, email, company_level, status FROM users ORDER BY id DESC");
        $members = $stmt->fetchAll();

        // Check if employee records exist in the database table
        if (count($members) > 0) {
            echo "<table>";
            echo "<thead>";
            echo "<tr><th>Username</th><th>First Name</th><th>Last Name</th><th>Email Address</th><th>Company Level</th><th>Status</th><th>Actions</th></tr>";
            echo "</thead>";
            echo "<tbody>";

            // Loop through each employee record and output table rows
            foreach ($members as $m) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($m['username'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($m['first_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($m['last_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($m['email'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($m['company_level'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($m['status'] ?? '') . "</td>";
                echo "<td>";
                // Action links passing the user ID parameter
                echo "<a href='edit_employee.php?id=" . urlencode($m['id'] ?? '') . "' class='btn-edit'>Edit</a> | ";
                echo "<a href='delete_employee.php?id=" . urlencode($m['id'] ?? '') . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this Employee?\");'>Delete</a>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        } else {
            // Fallback message if the table is empty
            echo "<p>No active employee records found in directory registry.</p>";
        }
    } catch (PDOException $e) {
        // Catch and display any SQL query execution errors safely
        echo "<p style='color: #ef4444;'>Error querying database: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</div>

<?php include '../templates/footer_template.php'; ?>