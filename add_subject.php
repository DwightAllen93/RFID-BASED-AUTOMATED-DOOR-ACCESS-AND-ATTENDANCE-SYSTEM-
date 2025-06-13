<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $status = 0; 
    $created_user = $_POST['created_user']; // Get user ID from form input

    // Debugging: Log the user ID
    error_log("Adding subject by user ID: " . $created_user);

    $sql = "INSERT INTO subject (subject_name, status, created_user, created_at) 
            VALUES ('$subject_name', '$status', '$created_user', NOW())";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Subject added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding subject: " . mysqli_error($conn);
    }

    header("Location: dashboard.php");
    exit();
}
?>
