<?php
include('config.php');

// Validate and retrieve POST data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$rfid = isset($_POST['rfid_number']) ? trim($_POST['rfid_number']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$updated_at = date("Y-m-d H:i:s");

// Optional: Validate field lengths based on DB schema (e.g. max rfid_number length)
if (strlen($rfid) > 50) {
    echo "<script>alert('RFID number too long.'); window.history.back();</script>";
    exit();
}

// Use prepared statement to avoid SQL injection
$stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, rfid_number = ?, email = ?, updated_at = ? WHERE id = ?");
$stmt->bind_param("sssssi", $first_name, $last_name, $rfid, $email, $updated_at, $id);

if ($stmt->execute()) {
    echo "<script>alert('Update successful!'); window.history.back();</script>";
} else {
    echo "<script>alert('Error updating user: " . $stmt->error . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
