<?php
include('config.php');
$id = intval($_POST['id']);
$query = "UPDATE user SET status = 1 WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
?>
