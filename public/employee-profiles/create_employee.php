<?php

session_start();

$errors = [];
$success = false;
$member = [];

// Security Check: Make sure the user actually used POST to get here
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Check each field using empty() and trim()
    // trim() removes accidental spaces (like if a user just presses the spacebar)
    if (empty(trim($_POST['first_name']))) {
        $errors[] = "First Name is required.";
    }

    if (empty(trim($_POST['last_name']))) {
        $errors[] = "Last Name is required.";
    }

    if (empty(trim($_POST['email']))) {
        $errors[] = "Email Address is required.";
    }

    if (empty(trim($_POST['role']))) {
        $errors[] = "User Role is required.";
    }

    // DUPLICATE CHECK (Only run this if the user actually typed an email)
    if (!empty(trim($_POST['email']))) {
        $file = 'data/employee_list.json';
        $submitted_email = strtolower(trim($_POST['email'])); // Normalize email to lowercase

        // Open the file and look through existing members
        if (file_exists($file) && filesize($file) > 0) {
            $current_data = file_get_contents($file);
            $members_array = json_decode($current_data, true);

            // Loop through each member in the JSON file
            foreach ($members_array as $existing_member) {
                if (strtolower($existing_member['email']) === $submitted_email) {
                    // Match found! Toss a new error into our array
                    $errors[] = "A user with this email address already exists.";
                    break; // Stop looking, we found a duplicate
                }
            }
        }
    }

    if (!empty($errors)) {
        
        $errorMessage = "Please fix the following errors:\\n";
        foreach ($errors as $error) {
            $errorMessage .= "- " . $error . "\\n";
        }

        // Inject the JavaScript alert
        echo "<script>alert('$errorMessage');</script>";

    } else {
        
    // If NO errors, it's safe to process the data!
    //Open the "envelope" ($_POST) and extract the data using the HTML 'name' keys
  
        $member = [
            "first_name" => htmlspecialchars($_POST['first_name']),
            "last_name"  => htmlspecialchars($_POST['last_name']),
            "email"      => htmlspecialchars($_POST['email']),
            "role"       => htmlspecialchars($_POST['role'])
        ];

        $success = true;

        // --- 💾 SAVE TO JSON FILE 💾 ---
        $file = 'data/employee_list.json';
        
        // Read existing members if the file exists, otherwise start a fresh array
        if (file_exists($file) && filesize($file) > 0) {
            $current_data = file_get_contents($file);
            $members_array = json_decode($current_data, true);
        } else {
            $members_array = [];
        }

        // Add the new member to our list
        $members_array[] = $member;

        // Save the updated list back into the file
        file_put_contents($file, json_encode($members_array, JSON_PRETTY_PRINT));
        // -------------------------------------


        $_SESSION['flash_message'] = "Employee Added: " . $member['first_name'] . " " . $member['last_name'] . " (" . $member['role'] . ")";
            
       
        header("Location: view_employee.php");
        exit();  
}
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
<h2>Create New Employee Profile</h2>

    <a href="../index.php" class="btn">Back to Dashboard</a> 

<hr />

<div class="create_menu">
    <form action="create_employee.php" method="POST">
        
        <label>First Name:</label><br>
        <input type="text" name="first_name"><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name"><br><br>        

        <label>Email Address:</label><br>
        <input type="email" name="email"><br><br>

        <label>Employee Role:</label><br>
        <select name="role" id="role">
            <option value="" disabled selected>-- Select a Role --</option>
            <option value="Manager">Manager</option>
            <option value="Staff">Staff</option>
            <option value="Volunteer">Volunteer</option>
            </select>
            <br><br>
        <button type="submit" class="submit-btn">Create New Employee Profile</button>
    </form>
</div>
</body>
</html>