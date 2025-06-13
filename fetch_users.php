<?php
include 'config.php';

$sql = "SELECT id, first_name,last_name FROM users WHERE status=0";
$result = $conn->query($sql);

$users = [];
while($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>
