<?php
session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== 0) {
        $_SESSION['import_error'] = "File upload error.";
        header("Location: manage_student.php");
        exit();
    }

    $fileTmpPath = $_FILES['import_file']['tmp_name'];
    $csv = array_map('str_getcsv', file($fileTmpPath));
    $header = array_map('trim', array_shift($csv)); // Remove header row

    $errors = [];
    $inserted = 0;

    foreach ($csv as $index => $row) {
        $data = array_combine($header, $row);

        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['rfid_number'])) {
            $errors[] = "Row " . ($index + 2) . ": Missing required fields.";
            continue;
        }

        $first_name = trim($data['first_name']);
        $last_name = trim($data['last_name']);
        $rfid_number = trim($data['rfid_number']);
        $subject_ids = isset($data['subject_ids']) ? trim($data['subject_ids']) : null;
        $section = trim($data['section']);
        $school_year = trim($data['school_year']);
        $status = 0;
        $created_at = date("Y-m-d H:i:s");

        // Check for duplicate RFID
        $check = $conn->prepare("SELECT id FROM student WHERE rfid_number = ?");
        $check->bind_param("s", $rfid_number);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $errors[] = "Row " . ($index + 2) . ": Duplicate RFID Number ($rfid_number).";
            continue;
        }

        // Insert record
        $stmt = $conn->prepare("INSERT INTO student (first_name, last_name, rfid_number, subject_ids, section, school_year, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $first_name, $last_name, $rfid_number, $subject_ids, $section, $school_year, $status, $created_at);

        if ($stmt->execute()) {
            $inserted++;
        } else {
            $errors[] = "Row " . ($index + 2) . ": " . $stmt->error;
        }
    }

    $_SESSION['import_success'] = "$inserted students imported successfully.";
    if (!empty($errors)) {
        $_SESSION['import_error'] = implode("<br>", $errors);
    }

    header("Location: addstudent.php");
    exit();
}
?>
