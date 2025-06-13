<?php
// Start session to track user login status
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Database connection
include('config.php');

// Get the RFID number and subject ID from the POST request
$rfid_number = isset($_POST['rfid_number']) ? $_POST['rfid_number'] : '';
$subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';

// Get the user ID based on the RFID number
$user_sql = "SELECT id, first_name, last_name FROM user WHERE rfid_number = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("s", $rfid_number);
$stmt->execute();
$user_result = $stmt->get_result();

// Check if user exists
if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];

    // Check if attendance already exists for today (time_in or time_out)
    $attendance_sql = "SELECT id, time_in, time_out FROM attendance WHERE student_id = ? AND subject_id = ? AND DATE(timestamp) = CURDATE()";
    $stmt = $conn->prepare($attendance_sql);
    $stmt->bind_param("ii", $user_id, $subject_id);
    $stmt->execute();
    $attendance_result = $stmt->get_result();

    if ($attendance_result->num_rows > 0) {
        // Attendance already exists, show an error message
        $attendance = $attendance_result->fetch_assoc();
        if ($attendance['time_in'] && !$attendance['time_out']) {
            echo json_encode(['error' => 'You have already checked in today.']);
        } elseif (!$attendance['time_in'] && !$attendance['time_out']) {
            echo json_encode(['error' => 'You have already scanned today, please wait for time out.']);
        } else {
            echo json_encode(['error' => 'You have already checked out today.']);
        }
    } else {
        // No attendance exists for today, return user data to proceed with time in/out
        echo json_encode([
            'success' => true,
            'user_id' => $user_id,
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
        ]);
    }
} else {
    // If user does not exist, return an error message
    echo json_encode(['error' => 'Invalid RFID.']);
}
?>
