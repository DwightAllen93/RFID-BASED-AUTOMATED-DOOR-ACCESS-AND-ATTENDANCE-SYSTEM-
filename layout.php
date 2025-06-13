<?php
// Start session to track user login status
session_start();

// Check if the user is logged in by checking the session
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // If not logged in, redirect to index.php (login page)
    header('Location: index.php');
    exit();
}

// Assume that $user_id is retrieved from the session for the logged-in user
$user_id = $_SESSION['user_logged_in']; // This should be set when the user logs in
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png">
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">

    <?php include('navbar.php'); ?>

    <!-- Dashboard Content -->
    <div class="min-h-screen flex flex-col items-center justify-center py-10">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Conetent</h1>

       
            </div>


  

</body>

</html>
