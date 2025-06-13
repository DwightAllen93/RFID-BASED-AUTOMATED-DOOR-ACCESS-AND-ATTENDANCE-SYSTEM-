<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ad";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Retrieve POST data
$userId = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$rfid = isset($_POST['rfid']) ? $_POST['rfid'] : '';
$accessResult = isset($_POST['access_result']) ? $_POST['access_result'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';

// Insert data into database
$sql = "INSERT INTO access_logs (user_id, rfid_number, access_result, message, access_time) 
        VALUES ('$userId', '$rfid', '$accessResult', '$message', NOW())";

if ($conn->query($sql) === TRUE) {
    echo "Log inserted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>
