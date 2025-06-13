<?php
include('config.php');

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$rfid = $_POST['rfid_number'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$status = $_POST['status'];
$created_at = date("Y-m-d H:i:s");
$updated_at = date("Y-m-d H:i:s");

$sql = "INSERT INTO users (first_name, last_name, rfid_number, email, password, status, created_at, updated_at)
        VALUES ('$first_name', '$last_name', '$rfid', '$email', '$password', $status, '$created_at', '$updated_at')";

if (mysqli_query($conn, $sql)) {
    header("Location: adduser.php?success=1");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
