<?php
session_start();

if (isset($_GET['email']) && isset($_GET['date'])) {
    
    $email_to_delete = urldecode($_GET['email']);
    $date_to_delete = urldecode($_GET['date']);
    
    $file = '../../data/schedules.json';

    if (file_exists($file) && filesize($file) > 0) {
        $json_data = file_get_contents($file);
        $schedules = json_decode($json_data, true);

        $shift_found = false;
        $updated_schedules = [];

        foreach ($schedules as $shift) {
            
            if ($shift['email'] === $email_to_delete && $shift['date'] === $date_to_delete) {
                $shift_found = true; 
                continue;           
            }
            
            $updated_schedules[] = $shift;
        }

        if ($shift_found) {
            file_put_contents($file, json_encode($updated_schedules, JSON_PRETTY_PRINT));
            $_SESSION['flash_message'] = "Successfully removed shift on " . $date_to_delete . " for " . $email_to_delete;
        } else {
            $_SESSION['flash_message'] = "Error: Targeted shift record could not be located.";
        }
    } else {
        $_SESSION['flash_message'] = "Error: Database file is empty or missing.";
    }
} else {
    $_SESSION['flash_message'] = "Security Warning: Invalid request parameters.";
}

header("Location: schedule.php");
exit();