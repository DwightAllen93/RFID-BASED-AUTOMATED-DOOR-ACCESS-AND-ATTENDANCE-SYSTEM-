<?php
include 'config.php';

// Get the subject_id from the URL
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : 0;

// Fetch schedules that match the subject_id and have status = 0
$sql = "SELECT id, day, time, door FROM schedule WHERE subject_id = ? AND status = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

echo json_encode($schedules);

$stmt->close();
$conn->close();
?>
