<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include 'config.php'; // Include database connection

// Get the subject and date if set
$subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : '';
$attendance_date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : '';

// Prepare the SQL query with filters if applicable
$query = "SELECT a.id, u.first_name AS student_name, s.subject_name AS subject, 
                 a.rfid_number, a.status, a.timestamp, a.time_in, a.time_out
          FROM attendance a
          JOIN users u ON a.student_id = u.id
          JOIN subject s ON a.subject_id = s.id";

// Apply subject filter if selected
if ($subject_id) {
    $query .= " WHERE a.subject_id = '$subject_id'";
}

// Apply date filter if selected
if ($attendance_date) {
    if ($subject_id) {
        $query .= " AND DATE(a.timestamp) = '$attendance_date'";
    } else {
        $query .= " WHERE DATE(a.timestamp) = '$attendance_date'";
    }
}

$result = $conn->query($query);

// Fetch the data
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Output JSON response
echo json_encode(['data' => $data]);
?>
