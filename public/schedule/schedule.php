<?php
// Start session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration details for Docker MariaDB
$host = 'mariadb';
$db   = 'db_data_test';
$user = 'user';
$pass = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 1. Fetch current employees from database to populate our dropdown list
$stmt_emp = $pdo->query("SELECT id, first_name, last_name, email, company_level AS role FROM users ORDER BY first_name ASC");
$employees = $stmt_emp->fetchAll();

// 2. Handle adding a new shift via POST submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_email = trim($_POST['employee_email'] ?? '');
    $shift_date = trim($_POST['shift_date'] ?? '');
    $shift_time = trim($_POST['shift_time'] ?? '');

    if (!empty($employee_email) && !empty($shift_date) && !empty($shift_time)) {

        // Find employee's ID and first name based on selected email
        $user_id = 0;
        $first_name = "Unknown";
        foreach ($employees as $emp) {
            if ($emp['email'] === $employee_email) {
                $user_id = $emp['id'];
                $first_name = $emp['first_name'];
                break;
            }
        }

        // Handle custom shift times vs preset slots
        if ($shift_time === 'CUSTOM') {
            $start_clock = $_POST['custom_start'] ?? '08:00';
            $end_clock = $_POST['custom_end'] ?? '16:00';
            $display_time = "Custom Shift ($start_clock - $end_clock)";
        } else {
            $display_time = $shift_time;
            if (strpos($shift_time, 'Morning') !== false) {
                $start_clock = '08:00:00';
                $end_clock = '16:00:00';
            } elseif (strpos($shift_time, 'Evening') !== false) {
                $start_clock = '16:00:00';
                $end_clock = '00:00:00';
            } else {
                $start_clock = '00:00:00';
                $end_clock = '08:00:00';
            }
        }

        // Insert the new schedule record into the database
        $insert_stmt = $pdo->prepare("INSERT INTO schedules (user_id, email, first_name, shift_date, shift_time, start_clock, end_clock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->execute([
            $user_id,
            $employee_email,
            $first_name,
            $shift_date,
            $display_time,
            $start_clock,
            $end_clock
        ]);

        $_SESSION['flash_message'] = "Shift successfully assigned!";
        header("Location: schedule.php");
        exit();
    }
}

// 3. Fetch existing schedules from database to display them in the table
$stmt_sched = $pdo->query("SELECT * FROM schedules ORDER BY shift_date DESC");
$current_schedules = $stmt_sched->fetchAll();

$page_title = "Employee Schedules";
include __DIR__ . '/../templates/header_template.php';
?>

<div class="dashboard-wrapper">
    <h2>Assign a New Shift</h2>
    <a href="../index.php" class="btn">Back to Dashboard</a>
    <hr />
    <br>

    <?php
    if (isset($_SESSION['flash_message'])) {
        echo '<div style="background-color: #d1fae5; color: #065f46; padding: 10px; margin-bottom: 15px; border-radius: 5px;">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
        unset($_SESSION['flash_message']);
    }
    ?>

    <form action="schedule.php" method="POST">
        <label>Select Employee:</label><br>
        <select name="employee_email" required>
            <option value="" disabled selected>-- Choose an Employee --</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?php echo htmlspecialchars($emp['email']); ?>">
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
                <tr>
                    <td colspan="5">No shifts assigned yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($current_schedules as $shift): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shift['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($shift['email']); ?></td>
                        <td><?php echo htmlspecialchars($shift['shift_date']); ?></td>
                        <td><?php echo htmlspecialchars($shift['shift_time']); ?></td>
                        <td>
                            <a href="edit_schedule.php?id=<?php echo urlencode($shift['id']); ?>" class="btn-edit">Edit</a>
                            <a href="delete_schedule.php?id=<?php echo urlencode($shift['id']); ?>"
                                class="btn-delete"
                                onclick="return confirm('Are you sure you want to delete this specific shift?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
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

            <?php
            $jsEvents = [];
            foreach ($current_schedules as $shift) {
                $start_time = isset($shift['start_clock']) ? $shift['start_clock'] : '08:00:00';
                $end_time = isset($shift['end_clock']) ? $shift['end_clock'] : '16:00:00';

                $className = 'shift-custom';
                if (strpos($shift['shift_time'], 'Morning') !== false) {
                    $className = 'shift-morning';
                } elseif (strpos($shift['shift_time'], 'Evening') !== false) {
                    $className = 'shift-evening';
                } elseif (strpos($shift['shift_time'], 'Night') !== false) {
                    $className = 'shift-night';
                }

                $end_date = $shift['shift_date'];
                if (strtotime($end_time) <= strtotime($start_time)) {
                    $end_date = date('Y-m-d', strtotime($shift['shift_date'] . ' +1 day'));
                }

                $fullDescription = $shift['first_name'] . " - " . $shift['shift_time'];

                $jsEvents[] = [
                    'title' => $shift['first_name'] . " (" . $start_time . " - " . $end_time . ")",
                    'start' => $shift['shift_date'] . 'T' . $start_time,
                    'end' => $end_date . 'T' . $end_time,
                    'className' => $className,
                    'description' => $fullDescription
                ];
            }
            ?>

            var rosterEvents = <?php echo json_encode($jsEvents); ?>;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                allDaySlot: false,
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
                        info.el.setAttribute('data-tooltip', info.event.extendedProps.description);
                    }
                }
            });

            calendar.render();
        });
    </script>
</div>
</div>

<?php include __DIR__ . '/../templates/footer_template.php'; ?>