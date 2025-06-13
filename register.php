<?php

include('config.php');

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $rfid_number = mysqli_real_escape_string($conn, $_POST['rfid_number']);
    $student_number = mysqli_real_escape_string($conn, $_POST['student_number']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_query = "SELECT * FROM users WHERE student_number = '$student_number' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error_message = "Student Number or Email already exists. Please use a different one.";
    } else {
        $query = "INSERT INTO users (first_name, last_name, rfid_number, student_number, password, email)
                  VALUES ('$first_name', '$last_name', '$rfid_number', '$student_number', '$hashed_password', '$email')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registration successful! You can now log in.');</script>";
            echo "<script>window.location.href = 'login.php';</script>";
        } else {
            $error_message = "Error occurred during registration. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="./css/3.4.16"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-4 text-gray-800 text-center">Register</h1>
        
        <!-- Error Message -->
        <?php if ($error_message): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" id="first_name" name="first_name" required
                    class="block w-full px-3 py-2 border rounded-md text-gray-700 focus:ring focus:ring-green-300">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" id="last_name" name="last_name" required
                    class="block w-full px-3 py-2 border rounded-md text-gray-700 focus:ring focus:ring-green-300">
            </div>
            <div>
                <label for="rfid_number" class="block text-sm font-medium text-gray-700">RFID Number</label>
                <input type="text" id="rfid_number" name="rfid_number" required
                    class="block w-full px-3 py-2 border rounded-md text-gray-700 focus:ring focus:ring-green-300">
            </div>
            <div>
                <label for="student_number" class="block text-sm font-medium text-gray-700">Student Number</label>
                <input type="text" id="student_number" name="student_number" required
                    class="block w-full px-3 py-2 border rounded-md text-gray-700 focus:ring focus:ring-green-300">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        class="block w-full px-3 py-2 border rounded-md text-gray-700 focus:ring focus:ring-green-300">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <input type="checkbox" id="show_password" class="mr-2" onclick="togglePassword()">
                        <label for="show_password" class="text-sm text-gray-500 cursor-pointer">Show</label>
                    </div>
                </div>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required
                    class="block w-full px-3 py-2 border rounded-md text-gray-700 focus:ring focus:ring-green-300">
            </div>
            <button type="submit"
                class="w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 transition">
                Register
            </button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-4">
            Already have an account? <a href="login.php" class="text-green-500 hover:underline">Login here</a>.
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
