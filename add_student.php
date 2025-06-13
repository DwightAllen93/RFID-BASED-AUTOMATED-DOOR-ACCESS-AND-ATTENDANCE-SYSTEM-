<?php
session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $rfid_number = trim($_POST['rfid_number']);
    $section = trim($_POST['section']);
    $school_year = trim($_POST['school_year']);
    $status = 0;

    $stmt = $conn->prepare("INSERT INTO student (first_name, last_name, rfid_number, section, school_year, status) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("sssssi", $first_name, $last_name, $rfid_number, $section, $school_year, $status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>