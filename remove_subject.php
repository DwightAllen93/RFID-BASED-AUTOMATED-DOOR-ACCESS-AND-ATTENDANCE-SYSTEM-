<?php
// Start session
session_start();

// Include database connection
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Get the subject ID from the POST request
if (isset($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];

    // Update the subject status to 1 (Inactive)
    $sql = "UPDATE subject SET status = 1 WHERE id = $subject_id";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to the dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
