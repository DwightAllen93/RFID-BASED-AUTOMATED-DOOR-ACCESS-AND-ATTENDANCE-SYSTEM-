<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include('config.php');

$first_name = mysqli_real_escape_string($conn, $_POST['first_name'] ?? '');
$last_name = mysqli_real_escape_string($conn, $_POST['last_name'] ?? '');
$rfid_number = mysqli_real_escape_string($conn, $_POST['rfid_number'] ?? '');
$section = mysqli_real_escape_string($conn, $_POST['section'] ?? '');
$school_year = mysqli_real_escape_string($conn, $_POST['school_year'] ?? '');
$status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

if (!$first_name || !$last_name || !$rfid_number || !$section || !$school_year) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
    exit();
}

// Insert query
$query = "INSERT INTO student (first_name, last_name, rfid_number, section, school_year, status) VALUES ('$first_name', '$last_name', '$rfid_number', '$section', '$school_year', $status)";
if (mysqli_query($conn, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
?>
