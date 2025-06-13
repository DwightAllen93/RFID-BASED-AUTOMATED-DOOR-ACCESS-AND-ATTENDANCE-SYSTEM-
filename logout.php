<?php
// Start session and destroy it to log the user out
session_start();
session_destroy();  // Destroy all session data
header('Location: index.php');  // Redirect to the login page
exit();
?>
