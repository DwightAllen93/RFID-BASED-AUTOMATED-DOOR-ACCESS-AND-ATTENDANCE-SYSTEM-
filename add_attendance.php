<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = intval($_POST['subject_id']);
    $rfid = trim($_POST['rfid']);
    $timestamp = $_POST['timestamp'];
    $date = date('Y-m-d', strtotime($timestamp));

    // 1. Validate required fields
    if (empty($rfid) || empty($timestamp) || empty($subject_id)) {
        $_SESSION['attendance_error'] = "All fields are required.";
        header("Location: attendance.php?subject_id=$subject_id&date=$date");
        exit();
    }

    // 2. Check RFID existence
    $stmt = $conn->prepare("SELECT id FROM user WHERE rfid_number = ?");
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $_SESSION['attendance_error'] = "Invalid RFID: user not found.";
        header("Location: attendance.php?subject_id=$subject_id&date=$date");
        exit();
    }

    $user_id = $result->fetch_assoc()['id'];

    // 3. Check for duplicate
    $check = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND subject = ? AND timestamp = ?");
    $check->bind_param("iis", $user_id, $subject_id, $timestamp);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['attendance_error'] = "Duplicate attendance already recorded.";
        header("Location: attendance.php?subject_id=$subject_id&date=$date");
        exit();
    }

    // 4. Insert record
    $insert = $conn->prepare("INSERT INTO attendance (rfid, user_id, subject, timestamp) VALUES (?, ?, ?, ?)");
    $insert->bind_param("siis", $rfid, $user_id, $subject_id, $timestamp);

    if ($insert->execute()) {
        $_SESSION['attendance_success'] = "Attendance recorded successfully.";
        header("Location: attendance.php?subject_id=$subject_id&date=$date");
        exit();
    } else {
        $_SESSION['attendance_error'] = "Failed to record attendance.";
        header("Location: attendance.php?subject_id=$subject_id&date=$date");
        exit();
    }
}
?>
