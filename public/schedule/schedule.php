<?php
session_start();

// 1. Fetch current employees to populate our dropdown list
$members_file = '../../data/employee_list.json';
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
        $schedules_file = '../../data/schedules.json';
        
        if (file_exists($schedules_file) && filesize($schedules_file) > 0) {
            $schedules = json_decode(file_get_contents($schedules_file), true);
        } else {
            $schedules = [];
        }

        $first_name = "Unknown"; 
        foreach ($employees as $emp) {
            if ($emp['email'] === $employee_email) {
                $first_name = $emp['first_name'];
                break; 
            }
        }

        if ($shift_time === 'CUSTOM') {
            $start_clock = $_POST['custom_start']; 
            $end_clock = $_POST['custom_end'];    
            $display_time = "Custom Shift ($start_clock - $end_clock)";
        } else {
            $display_time = $shift_time;
            if (strpos($shift_time, 'Morning') !== false) {
                $start_clock = '08:00'; $end_clock = '16:00';
            } elseif (strpos($shift_time, 'Evening') !== false) {
                $start_clock = '16:00'; $end_clock = '00:00';
            } else {
                $start_clock = '00:00'; $end_clock = '08:00';
            }
        }

        // Add the structured entry into schedules.json
        $schedules[] = [
            "first_name" => htmlspecialchars($first_name),
            "email" => htmlspecialchars($employee_email),
            "date" => htmlspecialchars($shift_date),
            "shift_time" => htmlspecialchars($display_time), 
            "start_clock" => htmlspecialchars($start_clock), // For FullCalendar mapping
            "end_clock" => htmlspecialchars($end_clock)      // For FullCalendar mapping
        ];

        file_put_contents($schedules_file, json_encode($schedules, JSON_PRETTY_PRINT));
        
        $_SESSION['flash_message'] = "Shift successfully assigned!";
        header("Location: schedule.php");
        exit();
    }
}

// 3. Fetch existing schedules to display them in a table below the form
$schedules_file = '../../data/schedules.json';
$current_schedules = [];
if (file_exists($schedules_file) && filesize($schedules_file) > 0) {
    $current_schedules = json_decode(file_get_contents($schedules_file), true);
}
 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Schedules</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
</head>
<body>
    <div class="dashboard-wrapper">
    <h2>Assign a New Shift</h2>
    <a href="../index.php" class="btn">Back to Dashboard</a>
    <hr />
    <br>
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
        <select name="shift_time" id="shift_time_select" required onchange="toggleCustomTimeFields()">
            <option value="" disabled selected>-- Select Time Slot --</option>
            <option value="Morning (08:00 - 16:00)">Morning (08:00 - 16:00)</option>
            <option value="Evening (16:00 - 00:00)">Evening (16:00 - 00:00)</option>
            <option value="Night (00:00 - 08:00)">Night (00:00 - 08:00)</option>
            <option value="CUSTOM">Custom Shift Hours...</option>
        </select>
        <br><br>
        <div id="custom_time_container" style="display: none; gap: 15px; margin-bottom: 20px;">
            <div>
                <small>Start Time</small><br>
                <input type="time" name="custom_start" id="custom_start">
            </div>
            <div>
                <small>End Time</small><br>
                <input type="time" name="custom_end" id="custom_end">
            </div>
        </div>            
        <button type="submit" class="submit-btn">Assign Shift</button>
    </form>
<script>
    function toggleCustomTimeFields() {
        var selectField = document.getElementById('shift_time_select');
        var customContainer = document.getElementById('custom_time_container');
        var startInput = document.getElementById('custom_start');
        var endInput = document.getElementById('custom_end');

        if (selectField.value === 'CUSTOM') {
            customContainer.style.display = 'flex';
            startInput.required = true;
            endInput.required = true;
        } else {
            customContainer.style.display = 'none';
            startInput.required = false;
            endInput.required = false;
        }
    }
    </script>
    <hr>

    <h2>Current Schedule</h2>
    <table class="table">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Employee Email</th>
                <th>Date</th>
                <th>Shift Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($current_schedules)): ?>
                <tr><td colspan="5">No shifts assigned yet.</td></tr>
            <?php else: ?>
                <?php foreach ($current_schedules as $shift): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shift['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($shift['email']); ?></td>
                        <td><?php echo htmlspecialchars($shift['date']); ?></td>
                        <td><?php echo htmlspecialchars($shift['shift_time']); ?></td>
                        <td>
                            <a href="edit_schedule.php?email=<?php echo urlencode($shift['email']); ?>" class="btn-edit">Edit</a>
                            <a href="delete_schedule.php?email=<?php echo urlencode($shift['email']); ?>&date=<?php echo urlencode($shift['date']); ?>" 
                            class="btn-delete" 
                            onclick="return confirm('Are you sure you want to delete this specific shift?');">
                            Delete
                            </a>
                        </td>
            
                </tr><?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <br>
    <hr>
    <h2>Roster Calendar View</h2>
    <div id="calendar"></div>         

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        // Let PHP assemble the data cleanly without breaking editor scripts
        <?php
        $jsEvents = [];
        foreach ($current_schedules as $shift) {
            // 1. Extract saved clock times (fall back to defaults if parsing old files)
            $start_time = isset($shift['start_clock']) ? $shift['start_clock'] . ':00' : '08:00:00';
            $end_time = isset($shift['end_clock']) ? $shift['end_clock'] . ':00' : '16:00:00';

            // 2. Intelligently color code templates vs custom configurations
            $className = 'shift-custom'; // Default color class for completely custom hours
            if (strpos($shift['shift_time'], 'Morning') !== false) {
                $className = 'shift-morning';
            } elseif (strpos($shift['shift_time'], 'Evening') !== false) {
                $className = 'shift-evening';
            } elseif (strpos($shift['shift_time'], 'Night') !== false) {
                $className = 'shift-night';
            }

            // 3. Handle overnight shifts crossing midnight (e.g., 22:00 to 06:00)
            $end_date = $shift['date'];
            if (strtotime($end_time) <= strtotime($start_time)) {
                $end_date = date('Y-m-d', strtotime($shift['date'] . ' +1 day'));
            }

            $fullDescription = $shift['first_name'] . " - " . $shift['shift_time'];

            $jsEvents[] = [
                'title' => $shift['first_name'] . " ( " . $start_time . " - " . $end_time . " ) ", // Keeps the cell view looking clean
                'start' => $shift['date'] . 'T' . $start_time,
                'end' => $end_date . 'T' . $end_time,
                'className' => $className,
                'description' => $fullDescription 
            ];
        }
        ?>

        var rosterEvents = <?php echo json_encode($jsEvents); ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            allDaySlot: false, // Prevents custom shifts from getting stuck at the top
            displayEventTime: false,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week'
            },
            events: rosterEvents,
            eventDidMount: function(info) {
                if (info.event.extendedProps.description) {
                    // Attaches a system title string to the element container for hover cards
                    info.el.setAttribute('data-tooltip', info.event.extendedProps.description);
                }
            }

        });

        calendar.render();
    });
    </script>
</div>
<?php include '../templates/footer_template.php'; ?>