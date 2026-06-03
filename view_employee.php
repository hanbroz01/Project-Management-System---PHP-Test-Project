<?php
session_start(); 
?>
<!DOCTYPE html>
<html>
<head>
   <title>View Employees</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>

        $(document).ready(function() {
            toastr.options = {
                "positionClass": "toast-top-right",
                "timeOut": "4000"
            };
        });
    </script>
</head>
<body>

    <h2>View Employee Profiles</h2>
    
    <a href="index.php" class="btn">Back to Dashboard</a>
    <a href="create_employee.php" class="btn">Create New Employee Profile</a>
<hr />
    <?php
    $file = 'employee_list.json';

    // 1. Check if the file exists and isn't empty
    if (file_exists($file) && filesize($file) > 0) {
        
        // 2. Read the file content
        $json_data = file_get_contents($file);
        
        // 3. Convert the JSON text back into a usable PHP array
        $members = json_decode($json_data, true);

        // 4. Start building the HTML table
        echo "<table>";
        echo "<thead>";
        echo "<tr><th>First Name</th><th>Last Name</th><th>Email Address</th><th>Role</th><th>Actions</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        // 5. Loop through every employee inside the JSON and output their row
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

       
    }}

    ?>
<script>
        <?php if (isset($_SESSION['flash_message'])): ?>
            toastr.success("<?php echo $_SESSION['flash_message']; ?>");
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>