<!DOCTYPE html>
<html>
<head>
    <title>View Members</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h2>Registered Members</h2>
    
    <a href="index.php" class="btn">Back to Dashboard</a>
    <a href="create_employee.php" class="btn">Create Member Profile</a>

    <?php
    $file = 'members.json';

    // 1. Check if the file exists and isn't empty
    if (file_exists($file) && filesize($file) > 0) {
        
        // 2. Read the file content
        $json_data = file_get_contents($file);
        
        // 3. Convert the JSON text back into a usable PHP array
        $members = json_decode($json_data, true);

        // 4. Start building the HTML table
        echo "<table>";
        echo "<thead>";
        echo "<tr><th>First Name</th><th>Last Name</th><th>Email Address</th><th>User Role</th><th>Actions</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        // 5. Loop through every member inside the JSON and output their row
        foreach ($members as $m) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($m['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($m['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($m['email']) . "</td>";
            echo "<td>" . htmlspecialchars($m['role']) . "</td>";
            echo "<td>";
            echo "<a href='edit_member.php?email=" . urlencode($m['email']) . "' class='btn-edit'>Edit</a>";
            echo "<a href='delete_member.php?email=" . urlencode($m['email']) . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>";
            echo "</td>";
            echo "</tr>";

       
    }}

var_dump($members);
    ?>

</body>
</html>