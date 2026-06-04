<?php
session_start();

if (isset($_GET['email'])) {
    $email_to_delete = urldecode($_GET['email']);
    $file = 'employee_list.json';

    if (file_exists($file) && filesize($file) > 0) {
        $json_data = file_get_contents($file);
        $members = json_decode($json_data, true);

        $user_found = false;
        $deleted_name = "";
        $updated_members = [];

        foreach ($members as $m) {
            if ($m['email'] === $email_to_delete) {
                $user_found = true; 
                $deleted_name = $m['first_name'] . ' ' . $m['last_name'];
                continue;           
            }
            $updated_members[] = $m;
        }

        if ($user_found) {
            file_put_contents($file, json_encode($updated_members, JSON_PRETTY_PRINT));
            $_SESSION['flash_message'] = "Successfully deleted Employee: " . htmlspecialchars($deleted_name) . " with email " . $email_to_delete;
        } else {
            $_SESSION['flash_message'] = "Error: User could not be found.";
        }
    }
}

// Redirect back to the display view page
header("Location: view_employee.php");
exit();