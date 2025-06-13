<?php
// Start session to track user login status
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Get the logged-in user ID
$user_id = $_SESSION['user_logged_in'];

include('config.php');

// Count instructors from "users" table
$instructor_count_query = "SELECT COUNT(*) AS count FROM users WHERE status = 0";
$instructor_result = mysqli_query($conn, $instructor_count_query);
$instructor_count = mysqli_fetch_assoc($instructor_result)['count'];
// Count subjects from "subject" table
$subject_count_query = "SELECT COUNT(*) AS count FROM subject WHERE status = 0";
$subject_result = mysqli_query($conn, $subject_count_query);
$subject_count = mysqli_fetch_assoc($subject_result)['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <link rel="icon" type="image/x-icon" href="./img/l1.png" />
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet" />

    <!-- Hover animation for buttons -->
    <style>
        /* Hover animation to make buttons bigger */
        button, .inline-block {
            transition: transform 0.3s ease;
        }

        button:hover, .inline-block:hover {
            transform: scale(1.1); /* Makes the button bigger */
        }
    </style>
</head>

<body class="bg-gray-100 font-sans relative">

    <!-- Background image div inserted here -->
    <div class="absolute inset-0 -z-20 bg-cover bg-center" style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')"></div>

    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-10 min-h-screen relative z-10">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Manage Subjects</h1>

            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <!-- Instructors Card -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Instructors</h2>
                    <p class="text-3xl font-bold text-green-600"><?php echo $instructor_count; ?></p>
                </div>

                <!-- Subjects Card -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Subjects</h2>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $subject_count; ?></p>
                </div>
            </div>

            <!-- Add Subject Button -->
            <button id="open-modal" class="text-white px-4 py-2 rounded-md mb-6 hover:bg-red-900 transition" style="background-color: #4B0000;">
                Add Subject
            </button>

            <!-- View Students Button -->
            <a href="student.php" class="inline-block text-white px-4 py-2 rounded-md mb-6 transition hover:bg-[#330000]" style="background-color: #4B0000;">
                View Students
            </a>

            <!-- Subject Table -->
            <div class="overflow-x-auto">
                <table id="subject-table" class="display w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject Name</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include('config.php');
                        $sql = "SELECT subject.id, subject.subject_name, subject.status, subject.created_user,
                                users.first_name, users.last_name
                                FROM subject
                                JOIN users ON subject.created_user = users.id
                                WHERE subject.status = 0";
                        $result = mysqli_query($conn, $sql);

                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['subject_name'] . "</td>";
                            echo "<td>" . ($row['status'] == 1 ? 'Inactive' : 'Active') . "</td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            echo "<td>";
                            echo "<a href='managesubject.php?id=" . $row['id'] . "' class='text-blue-500 hover:underline'>Manage</a> | ";
                            echo "<form action='remove_subject.php' method='POST' class='inline'>
                                    <input type='hidden' name='subject_id' value='" . $row['id'] . "'>
                                    <button type='button' class='text-red-500 hover:underline' onclick='confirmRemove(event)'>Remove</button>
                                  </form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal -->
            <div id="subject-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
                <div class="bg-white p-6 rounded-lg w-11/12 sm:w-96">
                    <h2 class="text-2xl font-bold mb-4">Add New Subject</h2>
                    <form action="add_subject.php" method="POST">
                        <div class="mb-4">
                            <label for="subject_name" class="block text-sm font-medium text-gray-700">Subject Name</label>
                            <input type="text" id="subject_name" name="subject_name" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" />
                        </div>
                        <input type="hidden" name="created_user" value="<?php echo $user_id; ?>" />
                        <input type="hidden" name="status" value="0" />
                        <button type="submit" class="w-full px-4 py-2 bg-[#4B0000] text-white rounded-md hover:bg-[#3A0000]">
                            Save Subject
                        </button>
                    </form>
                    <button id="close-modal" class="mt-4 w-full px-4 py-2 bg-[#4B0000] text-white rounded-md hover:bg-[#3A0000]">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Confirmation before remove
        function confirmRemove(event) {
            event.preventDefault(); // Prevent form submission immediately
            const confirmation = confirm("Are you sure you want to remove this subject?");
            if (confirmation) {
                event.target.closest("form").submit(); // Submit the form if confirmed
            }
        }

        $(document).ready(function () {
            $('#subject-table').DataTable();

            $('#open-modal').click(function () {
                $('#subject-modal').removeClass('hidden');
            });

            $('#close-modal').click(function () {
                $('#subject-modal').addClass('hidden');
            });
        });
    </script>
</body>

</html>
