<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $subject_id = intval($_POST['subject_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $rfid_number = trim($_POST['rfid_number']);
    $section = trim($_POST['section']);
    $school_year = trim($_POST['school_year']);

    // You can switch 'student' to 'user' depending on actual table
    $stmt = $conn->prepare("UPDATE student SET first_name=?, last_name=?, rfid_number=?, section=?, school_year=? WHERE id=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssi", $first_name, $last_name, $rfid_number, $section, $school_year, $id);

    if ($stmt->execute()) {
        header("Location: student.php?subject_id=" . $subject_id);
        exit();
    } else {
        echo "<p class='text-red-500'>Error updating student: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
$conn->close();
?>