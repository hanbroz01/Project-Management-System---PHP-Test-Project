<?php
session_start();

// 1. Check if an email was actually passed in the URL address bar
if (isset($_GET['email'])) {
    $email_to_delete = urldecode($_GET['email']);
    $file = 'members.json';

    // 2. Read the current list of users from your JSON database
    if (file_exists($file) && filesize($file) > 0) {
        $json_data = file_get_contents($file);
        $members = json_decode($json_data, true);

        $user_found = false;
        $updated_members = [];

        // 3. Loop through your users. 
        // Copy everyone over EXCEPT the person matching the email to delete.
        foreach ($members as $m) {
            if ($m['email'] === $email_to_delete) {
                $user_found = true; // Flag that we caught the targeted user
                continue;           // Skip adding them to our new list!
            }
            $updated_members[] = $m;
        }

        // 4. Save the updated list back into your JSON file
        if ($user_found) {
            file_put_contents($file, json_encode($updated_members, JSON_PRETTY_PRINT));
            
            // Set a Toastr success message to display on the dashboard!
            $_SESSION['flash_message'] = "Successfully deleted user: " . $email_to_delete;
        } else {
            $_SESSION['flash_message'] = "Error: User could not be found.";
        }
    }
}

header("Location: view_employee.php");
exit();