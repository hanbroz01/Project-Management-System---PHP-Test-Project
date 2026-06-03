<?php
session_start();

$file = 'employee_list.json';
$user_to_edit = null;
$errors = [];

// -------------------------------------------------------------------------
// STEP 1: LOAD USER DATA (When arriving from view_employee.php via GET)
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['email'])) {
    $email_to_find = urldecode($_GET['email']);

    if (file_exists($file) && filesize($file) > 0) {
        $json_data = file_get_contents($file);
        $members = json_decode($json_data, true);

        // Find the specific user
        foreach ($members as $m) {
            if ($m['email'] === $email_to_find) {
                $user_to_edit = $m;
                break;
            }
        }
    }
    
    if (!$user_to_edit) {
        $_SESSION['flash_message'] = "Error: User could not be found.";
        header("Location: view_employee.php"); 
        exit();
    }
}

// -------------------------------------------------------------------------
// STEP 2: SAVE UPDATED DATA (When the user clicks "Save Changes" via POST)
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $original_email = $_POST['original_email'];
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']); 
    $new_role = $_POST['role'];

    if (empty($new_first_name)) {
        $errors[] = "First name cannot be empty.";
    }
    if (empty($new_last_name)) {
        $errors[] = "Last name cannot be empty.";
    }

    if (empty($errors)) {
        if (file_exists($file) && filesize($file) > 0) {
            $json_data = file_get_contents($file);
            $members = json_decode($json_data, true);

            $user_found = false; 

            foreach ($members as $key => $m) {
                if ($m['email'] === $original_email) {
                    $members[$key]['first_name'] = htmlspecialchars($new_first_name);
                    $members[$key]['last_name'] = htmlspecialchars($new_last_name); 
                    $members[$key]['role'] = htmlspecialchars($new_role);
                    $user_found = true; 
                    break;
                }
            }

            if ($user_found) {
                file_put_contents($file, json_encode($members, JSON_PRETTY_PRINT));
                $_SESSION['flash_message'] = "Successfully edited Employee data!";
                
                header("Location: view_employee.php");
                exit();
            } else {
                $errors[] = "Error: User could not be found to update.";
            }
        }
    }
    
    // Handle error dropouts
    if (!empty($errors)) {
        $errorMessage = implode("\\n", $errors);
        echo "<script>alert('$errorMessage'); window.history.back();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body>

    <h2>Edit Employee Data</h2>
    <a href="index.php" class="btn">Back to Dashboard</a>
    <a href="view_employee.php" class="btn">Back to View all Employees</a>
    <hr/>
    <br>

    <div class="form-container">
        <form action="edit_employee.php" method="POST">
            
            <input type="hidden" name="original_email" value="<?php echo htmlspecialchars($user_to_edit['email'] ?? ''); ?>">

            <label><strong>Email Address (Cannot Be Changed):</strong></label>
            <p><?php echo htmlspecialchars($user_to_edit['email'] ?? ''); ?></p>

            <label>First Name:</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user_to_edit['first_name'] ?? ''); ?>">

            <label>Last Name:</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user_to_edit['last_name'] ?? ''); ?>">

            <label>Employee Role:</label>
            <select name="role">
                <option value="Manager" <?php echo (($user_to_edit['role'] ?? '') === 'manager') ? 'selected' : ''; ?>>Manager</option>
                <option value="Staff" <?php echo (($user_to_edit['role'] ?? '') === 'staff') ? 'selected' : ''; ?>>Staff</option>
                <option value="Volunteer" <?php echo (($user_to_edit['role'] ?? '') === 'volunteer') ? 'selected' : ''; ?>>Volunteer</option>
            </select>

            <button type="submit" class="btn-save">Save Changes</button>
            <a href="view_employee.php" class="btn-cancel">Cancel</a>
        </form>
    </div>

</body>
</html>