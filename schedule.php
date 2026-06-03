<?php
session_start();

// 1. Fetch current employees to populate our dropdown list
$members_file = 'members.json';
$employees = [];
if (file_exists($members_file) && filesize($members_file) > 0) {
    $employees = json_decode(file_get_contents($members_file), true);
}

// 2. Handle adding a new shift
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_email = $_POST['employee_email'];
    $shift_date = $_POST['shift_date'];
    $shift_time = $_POST['shift_time'];

    if (!empty($employee_email) && !empty($shift_date) && !empty($shift_time)) {
        $schedules_file = 'schedules.json';
        
        if (file_exists($schedules_file) && filesize($schedules_file) > 0) {
            $schedules = json_decode(file_get_contents($schedules_file), true);
        } else {
            $schedules = [];
        }

        $first_name = "Unknown"; // Default fallback if not found
        foreach ($employees as $emp) {
            if ($emp['email'] === $employee_email) {
                $first_name = $emp['first_name'];
                break; 
            }
        }

        // Add the new shift entry
        $schedules[] = [
            "first_name" => htmlspecialchars($first_name),
            "email" => htmlspecialchars($employee_email),
            "date" => htmlspecialchars($shift_date),
            "shift_time" => htmlspecialchars($shift_time)
        ];

        file_put_contents($schedules_file, json_encode($schedules, JSON_PRETTY_PRINT));
        
        $_SESSION['flash_message'] = "Shift successfully assigned!";
        header("Location: index.php");
        exit();
    }
}

// 3. Fetch existing schedules to display them in a table below the form
$schedules_file = 'schedules.json';
$current_schedules = [];
if (file_exists($schedules_file) && filesize($schedules_file) > 0) {
    $current_schedules = json_decode(file_get_contents($schedules_file), true);
}

var_dump($current_schedules);
//var_dump($schedules_file);
 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Schedules</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Assign a New Shift</h2>
    <a href="index.php" class="btn">Back to Dashboard</a>
    <br><br>

    <form action="schedule.php" method="POST">
        <label>Select Employee:</label><br>
        <select name="employee_email" required>
            <option value="" disabled selected>-- Choose an Employee --</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?php echo $emp['email']; ?>">
                    <?php echo htmlspecialchars($emp['first_name'] . " " . $emp['last_name']); ?> (<?php echo htmlspecialchars($emp['role']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Shift Date:</label><br>
        <input type="date" name="shift_date" required>
        <br><br>

        <label>Shift Time Slot:</label><br>
        <select name="shift_time" required>
            <option value="" disabled selected>-- Select Time Slot --</option>
            <option value="Morning (08:00 - 16:00)">Morning (08:00 - 16:00)</option>
            <option value="Evening (16:00 - 00:00)">Evening (16:00 - 00:00)</option>
            <option value="Night (00:00 - 08:00)">Night (00:00 - 08:00)</option>
        </select>
        <br><br>

        <button type="submit" class="submit-btn">Assign Shift</button>
    </form>

    <hr>

    <h2>Current Rostered Shifts</h2>
    <table border="1" cellpadding="10" style="width:100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Employee Email</th>
                <th>Date</th>
                <th>Shift Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($current_schedules)): ?>
                <tr><td colspan="4">No shifts assigned yet.</td></tr>
            <?php else: ?>
                <?php foreach ($current_schedules as $shift): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shift['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($shift['email']); ?></td>
                        <td><?php echo htmlspecialchars($shift['date']); ?></td>
                        <td><?php echo htmlspecialchars($shift['shift_time']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>