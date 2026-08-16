<?php
// Start session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Verify user is logged in and has Admin privileges
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
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

$shift_to_edit = null;

// 1. Handle incoming GET request to load the shift data
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $schedule_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ? LIMIT 1");
    $stmt->execute([$schedule_id]);
    $shift_to_edit = $stmt->fetch();

    if (!$shift_to_edit) {
        $_SESSION['flash_message'] = "Error: Shift could not be found.";
        header("Location: schedule.php");
        exit();
    }
}

// 2. Handle form submission via POST when saving changes
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $schedule_id = filter_var($_POST['schedule_id'] ?? 0, FILTER_VALIDATE_INT);
    $employee_email = trim($_POST['employee_email'] ?? '');
    $shift_date = trim($_POST['shift_date'] ?? '');
    $shift_time = trim($_POST['shift_time'] ?? '');

    if ($schedule_id && !empty($employee_email) && !empty($shift_date) && !empty($shift_time)) {

        // Fetch employee first name based on email
        $stmt_emp = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ? LIMIT 1");
        $stmt_emp->execute([$employee_email]);
        $emp_data = $stmt_emp->fetch();

        $user_id = $emp_data['id'] ?? 0;
        $first_name = $emp_data['first_name'] ?? 'Unknown';

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

        // Update the shift record in the database
        $update_stmt = $pdo->prepare("UPDATE schedules SET user_id = ?, email = ?, first_name = ?, shift_date = ?, shift_time = ?, start_clock = ?, end_clock = ? WHERE id = ?");
        $update_stmt->execute([
            $user_id,
            $employee_email,
            $first_name,
            $shift_date,
            $display_time,
            $start_clock,
            $end_clock,
            $schedule_id
        ]);

        $_SESSION['flash_message'] = "Shift successfully updated!";
        header("Location: schedule.php");
        exit();
    }
}

// Fetch all employees for the dropdown list
$stmt_emp_list = $pdo->query("SELECT first_name, last_name, email, company_level AS role FROM users ORDER BY first_name ASC");
$employees = $stmt_emp_list->fetchAll();

$page_title = "Edit Shift";
include __DIR__ . '/../templates/header_template.php';
?>

<div class="dashboard-wrapper">
    <h2>Edit Assigned Shift</h2>
    <a href="schedule.php" class="btn">Back to Schedule</a>
    <hr />
    <br>

    <form action="edit_schedule.php" method="POST">
        <!-- Hidden input to track the shift record ID -->
        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($shift_to_edit['id'] ?? ''); ?>">

        <label>Select Employee:</label><br>
        <select name="employee_email" required>
            <option value="" disabled>-- Choose an Employee --</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?php echo htmlspecialchars($emp['email']); ?>"
                    <?php echo (($shift_to_edit['email'] ?? '') === $emp['email']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($emp['first_name'] . " " . $emp['last_name']); ?> (<?php echo htmlspecialchars($emp['role']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Shift Date:</label><br>
        <input type="date" name="shift_date" value="<?php echo htmlspecialchars($shift_to_edit['shift_date'] ?? ''); ?>" required>
        <br><br>

        <label>Shift Time Slot:</label><br>
        <select name="shift_time" id="shift_time_select" required onchange="toggleCustomTimeFields()">
            <option value="" disabled>-- Select Time Slot --</option>
            <option value="Morning (08:00 - 16:00)" <?php echo (strpos($shift_to_edit['shift_time'] ?? '', 'Morning') !== false) ? 'selected' : ''; ?>>Morning (08:00 - 16:00)</option>
            <option value="Evening (16:00 - 00:00)" <?php echo (strpos($shift_to_edit['shift_time'] ?? '', 'Evening') !== false) ? 'selected' : ''; ?>>Evening (16:00 - 00:00)</option>
            <option value="Night (00:00 - 08:00)" <?php echo (strpos($shift_to_edit['shift_time'] ?? '', 'Night') !== false) ? 'selected' : ''; ?>>Night (00:00 - 08:00)</option>
            <option value="CUSTOM" <?php echo (strpos($shift_to_edit['shift_time'] ?? '', 'Custom') !== false) ? 'selected' : ''; ?>>Custom Shift Hours...</option>
        </select>
        <br><br>

        <div id="custom_time_container" style="display: <?php echo (strpos($shift_to_edit['shift_time'] ?? '', 'Custom') !== false) ? 'flex' : 'none'; ?>; gap: 15px; margin-bottom: 20px;">
            <div>
                <small>Start Time</small><br>
                <input type="time" name="custom_start" id="custom_start" value="<?php echo htmlspecialchars($shift_to_edit['start_clock'] ?? ''); ?>">
            </div>
            <div>
                <small>End Time</small><br>
                <input type="time" name="custom_end" id="custom_end" value="<?php echo htmlspecialchars($shift_to_edit['end_clock'] ?? ''); ?>">
            </div>
        </div>

        <button type="submit" class="submit-btn">Save Changes</button>
        <a href="schedule.php" class="btn" style="background-color: #6b7280; margin-left: 10px;">Cancel</a>
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
</div>
</div>

<?php include __DIR__ . '/../templates/footer_template.php'; ?>