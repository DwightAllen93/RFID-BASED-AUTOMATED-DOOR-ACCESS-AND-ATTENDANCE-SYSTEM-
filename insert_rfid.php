<?php
// Include database connection
include('config.php');

// Check if the request method is POST and the RFID is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfid'])) {
    // Clean the RFID input to retain only numeric values
    $rfid = preg_replace('/\D/', '', $_POST['rfid']); // Remove non-digit characters

    // Validate that the RFID is not empty
    if (empty($rfid)) {
        echo json_encode(['success' => false, 'message' => 'Invalid RFID!']);
        exit();
    }

    // Extract additional data from POST
    $first_name = $_POST['first_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $section = $_POST['section'] ?? null;
    $school_year = $_POST['school_year'] ?? null;

    // Validate required fields
    if (!$first_name || !$last_name || !$email || !$section || !$school_year) {
        echo json_encode(['success' => false, 'message' => 'All fields are required!']);
        exit();
    }

    // Insert data into the users table
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, rfid_number, email, section, school_year, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status = 0; // Default status for a new user
    $stmt->bind_param('ssssssi', $first_name, $last_name, $rfid, $email, $section, $school_year, $status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add user: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request!']);
    exit();
}
?>
