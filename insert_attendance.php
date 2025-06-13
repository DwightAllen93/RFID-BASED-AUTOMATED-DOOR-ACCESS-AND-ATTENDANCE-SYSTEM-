<?php
// Include the database configuration file
include('config.php');

// Set the timezone to Philippine Standard Time (PST)
date_default_timezone_set('Asia/Manila');

// Check if the necessary parameters (rfid_number, subject_id) are passed
if (isset($_POST['rfid_number'], $_POST['subject_id'], $_POST['attendance_type'])) {
    $rfid_number = trim($_POST['rfid_number']); // RFID number of the student
    $subject_id = $_POST['subject_id']; // Selected subject ID
    $attendance_type = $_POST['attendance_type']; // Either 'time_in' or 'time_out'

    // Get the current date and time in Philippine Standard Time (PST)
    $timestamp = date('Y-m-d H:i:s'); // Current timestamp for attendance (PST)

    // Fetch the user information based on the RFID number and subject_id
    $sql = "SELECT * FROM user WHERE rfid_number = ? AND subject_id = ? AND status = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $rfid_number, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Check attendance for the given user and subject
        if ($attendance_type === 'time_in') {
            // Check if attendance already exists for today
            $attendance_check_sql = "SELECT * FROM attendance 
                                     WHERE student_id = ? 
                                     AND subject_id = ? 
                                     AND DATE(timestamp) = CURDATE()";
            $stmt_check = $conn->prepare($attendance_check_sql);
            $stmt_check->bind_param('ii', $user_id, $subject_id);
            $stmt_check->execute();
            $attendance_check_result = $stmt_check->get_result();

            // If attendance already exists, show error
            if ($attendance_check_result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Attendance already recorded for today.']);
            } else {
                // Insert the attendance record with the 'time_in' timestamp
                $insert_sql = "INSERT INTO attendance (student_id, subject_id, timestamp) 
                               VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($insert_sql);
                $stmt_insert->bind_param('iis', $user_id, $subject_id, $timestamp);

                // Execute the insertion query
                if ($stmt_insert->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Attendance recorded (time_in) successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error recording attendance.']);
                }
            }
        }
        // If it's 'time_out', update the existing record with the 'time_out' timestamp
        else if ($attendance_type === 'time_out') {
            // Update the attendance record with the 'time_out' timestamp
            $update_sql = "UPDATE attendance 
                           SET time_out = ? 
                           WHERE student_id = ? 
                           AND subject_id = ? 
                           AND DATE(timestamp) = CURDATE() 
                           AND time_out IS NULL"; // Only update if timeout hasn't been set
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param('sii', $timestamp, $user_id, $subject_id);

            // Execute the update query
            if ($stmt_update->execute() && $stmt_update->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Attendance recorded (time_out) successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error or attendance already recorded.']);
            }
        } else {
            // If the attendance type is neither 'time_in' nor 'time_out'
            echo json_encode(['success' => false, 'message' => 'Invalid attendance type.']);
        }
    } else {
        // If no user is found based on RFID and subject
        echo json_encode(['success' => false, 'message' => 'Invalid RFID or subject.']);
    }
} else {
    // If required parameters are missing
    echo json_encode(['success' => false, 'message' => 'Invalid request. Missing parameters.']);
}
?>
