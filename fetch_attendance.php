<?php
// Include database connection
include('config.php');

// Check if the subject_id is sent via POST
if (isset($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];

    // Query to fetch attendance based on subject_id, including time_in and time_out
    $attendance_sql = "SELECT a.student_id, u.first_name, u.last_name, a.time_in, a.time_out, a.timestamp 
                        FROM attendance a
                        JOIN user u ON a.student_id = u.id
                        WHERE a.subject_id = ? AND DATE(a.timestamp) = CURDATE()"; // Filters today's attendance

    // Prepare and execute the query
    if ($stmt = $conn->prepare($attendance_sql)) {
        $stmt->bind_param('i', $subject_id); // Bind subject_id to the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if there is attendance data
        if ($result->num_rows > 0) {
            $attendance = [];

            // Fetch all attendance records
            while ($row = $result->fetch_assoc()) {
                $attendance[] = [
                    'student_id' => $row['student_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'time_in' => $row['time_in'] ? date('H:i:s', strtotime($row['time_in'])) : 'N/A', // Format time_in
                    'time_out' => $row['time_out'] ? date('H:i:s', strtotime($row['time_out'])) : 'N/A', // Format time_out
                    'timestamp' => date('Y-m-d H:i:s', strtotime($row['timestamp'])) // Format timestamp
                ];
            }

            // Return attendance data as JSON
            echo json_encode(['success' => true, 'attendance' => $attendance]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No records found']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare query']);
    }
}
?>
