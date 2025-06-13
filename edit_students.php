<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include('config.php');

$id = $_POST['id'] ?? null;
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$rfid_number = $_POST['rfid_number'] ?? '';
$section = $_POST['section'] ?? '';
$school_year = $_POST['school_year'] ?? '';


if (!$id || !$first_name || !$last_name || !$rfid_number || !$section || !$school_year) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$stmt = $conn->prepare("UPDATE student SET first_name=?, last_name=?, rfid_number=?, section=?, school_year=? WHERE id=?");
$stmt->bind_param("sssssi", $first_name, $last_name, $rfid_number, $section, $school_year, $id);




if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update student']);
}
?>
