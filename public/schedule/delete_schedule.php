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

// Check if an ID was passed via GET request
if (isset($_GET['id'])) {
    $schedule_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($schedule_id) {
        // Prepare and execute the delete query
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$schedule_id]);

        $_SESSION['flash_message'] = "Shift successfully deleted!";
    }
}

// Redirect back to the main schedule page
header("Location: schedule.php");
exit();
