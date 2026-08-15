<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Only let Admin delete
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_to_delete = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    // Database connection details
    $host = 'mariadb';
    $db   = 'db_data_test';
    $user = 'user';
    $pass = 'password';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // 1. Fetch user first to get their name for the confirmation message
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id_to_delete]);
        $user_data = $stmt->fetch();

        if ($user_data) {
            $deleted_name = $user_data['first_name'] . ' ' . $user_data['last_name'];

            // 2. Perform deletion by ID
            $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delete_stmt->execute([$id_to_delete]);

            $_SESSION['flash_message'] = "Successfully deleted Employee: " . htmlspecialchars($deleted_name);
        } else {
            $_SESSION['flash_message'] = "Error: User could not be found in the database.";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Database error during deletion: " . $e->getMessage();
    }
} else {
    $_SESSION['flash_message'] = "Error: No ID parameter provided for deletion.";
}

// Redirect back to view employee directory
header("Location: view_employee.php");
exit();
