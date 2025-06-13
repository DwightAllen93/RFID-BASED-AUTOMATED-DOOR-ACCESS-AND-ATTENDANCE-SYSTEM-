<?php
include('config.php');
$id = $_POST['user_id'];

// Soft delete (set status = 1)
$sql = "UPDATE users SET status = 1 WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    header("Location: adduser.php");
} else {
    echo "Error deleting record: " . mysqli_error($conn);
}
?>
