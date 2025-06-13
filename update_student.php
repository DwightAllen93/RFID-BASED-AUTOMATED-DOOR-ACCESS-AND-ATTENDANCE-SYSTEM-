<?php
require 'config.php'; // Ensure your database connection is included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $rfid_number = $_POST['rfid_number'];
    $section = $_POST['section'];
    $school_year = $_POST['school_year'];

    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($rfid_number) || empty($section) || empty($school_year)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit();
    }

    $sql = "UPDATE students SET first_name = ?, last_name = ?, rfid_number = ?, section = ?, school_year = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $rfid_number, $section, $school_year, $student_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Student updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database update failed: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
