<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include('config.php');

if (!isset($_POST['student_ids'], $_POST['subject_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$student_ids = $_POST['student_ids'];
$subject_id = $_POST['subject_id'];

// Validate inputs
if (!is_array($student_ids) || empty($student_ids)) {
    echo json_encode(['success' => false, 'message' => 'No students selected']);
    exit();
}

$subject_id = intval($subject_id);
if ($subject_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid subject']);
    exit();
}

// Prepare statement to get and update student
$get_stmt = $conn->prepare("SELECT subject_ids FROM student WHERE id = ?");
$update_stmt = $conn->prepare("UPDATE student SET subject_ids = ? WHERE id = ?");

foreach ($student_ids as $sid) {
    $sid = intval($sid);
    if ($sid <= 0) continue;

    // Get existing subject_ids for this student
    $get_stmt->bind_param("i", $sid);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $existing = $row['subject_ids'] ?? '';

        // Convert CSV to array, trim, and remove empties
        $subject_ids_arr = array_filter(array_map('trim', explode(',', $existing)));

        // Add new subject if not present
        if (!in_array($subject_id, $subject_ids_arr)) {
            $subject_ids_arr[] = $subject_id;
        }

        // Prepare CSV string again
        $new_subject_ids = implode(',', $subject_ids_arr);

        // Update student record
        $update_stmt->bind_param("si", $new_subject_ids, $sid);
        $update_stmt->execute();
    }
}

echo json_encode(['success' => true]);
