<?php
// Start session
session_start();

// Include the database connection
include('config.php'); // Assuming 'config.php' contains the database connection setup

// Get the POST data from the login form
$email = $_POST['email'];
$password = $_POST['password'];

// Prepare the SQL query to check if the email exists in the database
$query = "SELECT * FROM users WHERE email = ? LIMIT 1";  // Assuming 'users' table contains email and password fields
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email); // Bind email as a parameter

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Check if a user is found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify the password (assuming it's hashed in the database)
    if (password_verify($password, $user['password'])) {
        // Set session variables for logged-in user
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $user['email'];
        
        // Redirect to dashboard.php
        header("Location: dashboard.php");
        exit();
    } else {
        // Incorrect password
        $_SESSION['error_message'] = "Invalid email or password.";
    }
} else {
    // Email not found
    $_SESSION['error_message'] = "Invalid email or password.";
}

// Redirect back to the login page if authentication fails
header("Location: login.php");
exit();
?>
