<?php
// Include the database configuration
include('config.php');

// Check if the necessary parameters (rfid_number, subject_id) are passed
if (isset($_POST['rfid_number']) && isset($_POST['subject_id'])) {
    $rfid_number = trim($_POST['rfid_number']);  // Trim spaces around the RFID number
    $subject_id = $_POST['subject_id'];

    // Fetch user data from the user table, verifying both RFID and subject ID
    $sql = "SELECT * FROM user WHERE rfid_number = '$rfid_number' AND subject_id = '$subject_id' AND status = 0";
    $result = $conn->query($sql);

    // Check if a matching user is found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $student_id = $user['id'];  // Changed from user_id to student_id

        // Fetch attendance data for the user and subject
        $attendance_sql = "SELECT time_in, time_out FROM attendance WHERE student_id = '$student_id' AND subject_id = '$subject_id' AND DATE(timestamp) = CURDATE()";
        $attendance_result = $conn->query($attendance_sql);

        // Initialize attendance data
        $time_in = $time_out = null;
        if ($attendance_result->num_rows > 0) {
            $attendance = $attendance_result->fetch_assoc();
            $time_in = $attendance['time_in'];
            $time_out = $attendance['time_out'];
        }

        // Return user and attendance data as JSON
        echo json_encode([
            'student_id' => $user['id'],  // Changed from user_id to student_id
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'section' => $user['section'],
            'school_year' => $user['school_year'],
            'rfid_number' => $user['rfid_number'],
            'subject_name' => getSubjectName($user['subject_id'], $conn),
            'time_in' => $time_in ? date('h:i A', strtotime($time_in)) : 'Not yet checked in',  // Format time_in
            'time_out' => $time_out ? date('h:i A', strtotime($time_out)) : 'Not yet checked out'  // Format time_out
        ]);
    } else {
        // If no user is found, return an error message
        echo json_encode(['error' => 'Invalid RFID or subject.']);
    }
} else {
    echo json_encode(['error' => 'Missing RFID or subject ID.']);
}

// Function to get the subject name based on subject_id
function getSubjectName($subject_id, $conn) {
    $subject_sql = "SELECT subject_name FROM subject WHERE id = '$subject_id'";
    $subject_result = $conn->query($subject_sql);
    if ($subject_result->num_rows > 0) {
        $subject = $subject_result->fetch_assoc();
        return $subject['subject_name'];
    }
    return "Subject not found";
}
?>
