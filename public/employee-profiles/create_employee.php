<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Only let authenticated HR Managers access this page
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$errors = [];
$success = false;
$member = [];

// Base data path directory resolution helper
$file = __DIR__ . '/../../data/users.json';

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

    if (empty(trim($_POST['role'] ?? ''))) {
        $errors[] = "User Role is required.";
    }

    // DUPLICATE CHECK (Only run this if the user actually typed an email)
    if (!empty(trim($_POST['email']))) {
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
    // Fetch current file data array to calculate next auto-incremental ID values
        if (file_exists($file) && filesize($file) > 0) {
            $current_data = file_get_contents($file);
            $members_array = json_decode($current_data, true);
        } else {
            $members_array = [];
        }

        // 1. AUTO-GENERATE ID: Calculate unique token sequence strings
        $next_number = count($members_array) + 1;
        $generated_id = "USR" . str_pad($next_number, 3, "0", STR_PAD_LEFT);

        // 2. DEFAULT SECURITY ENCRYPTION: Lock account to universal setup password
        $default_raw_password = "Welcome123!"; 
        $secure_hash = password_hash($default_raw_password, PASSWORD_BCRYPT);

        // 3. SECURE PAYLOAD: Package up form properties alongside core server fields
        $member = [
            "id"           => $generated_id,
            "email"        => htmlspecialchars(strtolower(trim($_POST['email']))),
            "password"     => $secure_hash,
            "first_name"   => htmlspecialchars(trim($_POST['first_name'])),
            "last_name"    => htmlspecialchars(trim($_POST['last_name'])),
            "role"         => htmlspecialchars($_POST['role']),
            "access_level" => ($_POST['role'] === 'Manager') ? 'admin' : 'employee'
        ];

        $success = true;

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

$page_title = "Create Employee profile";
include __DIR__ . '/../templates/header_template.php';
?>

<h2>Create New Employee Profile</h2>

    <a href="../index.php" class="btn">Back to Dashboard</a> 

<hr />

<div class="create_menu">
    <form action="create_employee.php" method="POST">
        
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name"required><br><br>        

        <label>Email Address:</label><br>
        <input type="email" name="email" required placeholder="name@company.com"><br><br>

        <label>Employee Role:</label><br>
        <select name="role" id="role" required>
            <option value="" disabled selected>-- Select a Role --</option>
            <option value="Manager">Manager</option>
            <option value="Staff">Staff</option>
            <option value="Volunteer">Volunteer</option>
            </select>
            <br><br>
        <button type="submit" class="submit-btn">Create New Employee Profile</button>
    </form>
</div>
</div> <?php include '../templates/footer_template.php'; ?>
