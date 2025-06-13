<?php
include('config.php');
$id = intval($_POST['id']);
$query = "SELECT id, first_name, last_name, rfid_number FROM user WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
echo json_encode($student);
?>
