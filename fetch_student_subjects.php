<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include('config.php');

if (!isset($_GET['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID']);
    exit();
}

$student_id = intval($_GET['student_id']);
if ($student_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit();
}

// Get subject_ids from student record
$stmt = $conn->prepare("SELECT subject_ids FROM student WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $subject_ids_csv = $row['subject_ids'] ?? '';

    if (empty($subject_ids_csv)) {
        echo json_encode(['success' => true, 'subjects' => []]);
        exit();
    }

    // Prepare subject IDs for SQL IN clause
    $subject_ids = array_filter(array_map('intval', explode(',', $subject_ids_csv)));

    if (empty($subject_ids)) {
        echo json_encode(['success' => true, 'subjects' => []]);
        exit();
    }

    // Fetch subject names
    // Use prepared statements with IN is tricky, so build a safe placeholder string
    $placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
    $types = str_repeat('i', count($subject_ids));

    $sql = "SELECT id, subject_name FROM subject WHERE id IN ($placeholders) AND status = 0";
    $stmt = $conn->prepare($sql);

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$subject_ids);
    $stmt->execute();
    $subjects_result = $stmt->get_result();

    $subjects = [];
    while ($subject = $subjects_result->fetch_assoc()) {
        $subjects[] = $subject;
    }

    echo json_encode(['success' => true, 'subjects' => $subjects]);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
}
