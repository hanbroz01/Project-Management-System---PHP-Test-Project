<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Only let authenticated HR Managers view this management directory
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Choose custom tab title for this specific page
$page_title = "Employee Profiles"; 
include __DIR__ . '/../templates/header_template.php'; 
?>

    <h2>View Employee Profiles</h2>
    
    <a href="../index.php" class="btn">Back to Dashboard</a>
    <a href="create_employee.php" class="btn">Create New Employee Profile</a>
<hr />
    <?php
    $file = __DIR__ . '/../../data/users.json'; 

    if (file_exists($file) && filesize($file) > 0) {
        
        $json_data = file_get_contents($file);
        $members = json_decode($json_data, true);

        echo "<table>";
        echo "<thead>";
        echo "<tr><th>First Name</th><th>Last Name</th><th>Email Address</th><th>Role</th><th>Actions</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($members as $m) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($m['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($m['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($m['email']) . "</td>";
            echo "<td>" . htmlspecialchars($m['role']) . "</td>";
            echo "<td>";
            echo "<a href='edit_employee.php?email=" . urlencode($m['email']) . "' class='btn-edit'>Edit</a>";
            echo "<a href='delete_employee.php?email=" . urlencode($m['email']) . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this Employee?\");'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        } 
        
        echo "</tbody>";
        echo "</table>";
        
    } 
    ?>
    </div>
<?php include '../templates/footer_template.php'; ?>