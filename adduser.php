<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include('config.php');
$user_id = $_SESSION['user_logged_in'];

// ✅ Fetch user email for session
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['email'] = $user['email'];
}

// Fetch active users
$user_query = "SELECT * FROM users WHERE status = 0";
$user_result = mysqli_query($conn, $user_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png">
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        /* Fade-in animation */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 1.5s ease forwards;
        }

        /* Button hover scale */
        .btn-animate:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        /* Modal fade-in/out */
        .modal-show {
            animation: modalFadeIn 0.3s forwards;
        }

        .modal-hide {
            animation: modalFadeOut 0.3s forwards;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes modalFadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans relative">

    <!-- Background image div -->
    <div
        class="absolute inset-0 -z-20 bg-cover bg-center"
        style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')">
    </div>

    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-10 min-h-screen relative z-10 fade-in-up">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Manage Instructors</h1>

            <!-- Add User Button -->
            <button id="open-modal" 
                class="btn-animate text-white px-4 py-2 rounded-md hover:bg-[#3A0000] mb-6" 
                style="background-color: #4B0000;">
                Add Instructor
            </button>

            <!-- User Table -->
            <div class="overflow-x-auto">
                <table id="user-table" class="display w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>RFID Number</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($user_result)) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['rfid_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo $row['status'] == 0 ? 'Active' : 'Inactive'; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <?php
                                    $logged_in_email = $_SESSION['email'] ?? '';
                                    ?>
                                    <?php if ($logged_in_email === 'mmsudev@gmail.com'): ?>
                                        <?php if (in_array($row['email'], ['mmsudev@gmail.com', 'mmsuadmin@gmail.com'])): ?>
                                            <span class="text-gray-400 cursor-not-allowed">Locked</span>
                                        <?php else: ?>
                                            <button 
                                                class="edit-user-btn text-blue-500 hover:underline" 
                                                data-user='<?php echo json_encode($row); ?>'>
                                                Edit
                                            </button> |
                                            <form action="delete_user.php" method="POST" class="inline">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="text-red-500 hover:underline">Remove</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-11/12 sm:w-96 modal-content">
            <h2 class="text-2xl font-bold mb-4">Add Instructor</h2>
            <form action="insert_users.php" method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">RFID Number</label>
                    <input type="text" name="rfid_number" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <input type="hidden" name="status" value="0">
                <button type="submit" class="w-full bg-green-500 btn-animate text-white px-4 py-2 rounded-md hover:bg-green-600">
                    Save User
                </button>
            </form>
            <button id="close-modal" class="mt-4 w-full bg-red-500 btn-animate text-white px-4 py-2 rounded-md hover:bg-red-600">
                Close
            </button>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-11/12 sm:w-96 modal-content">
            <h2 class="text-2xl font-bold mb-4">Edit Instructor</h2>
            <form action="update_user.php" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="edit-first-name" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="edit-last-name" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">RFID Number</label>
                    <input type="text" name="rfid_number" id="edit-rfid" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="edit-email" required class="mt-1 w-full border px-4 py-2 rounded-md">
                </div>
                <button type="submit" 
                    class="w-full btn-animate text-white px-4 py-2 rounded-md hover:bg-[#3A0000]" 
                    style="background-color: #4B0000;">
                    Update Instructor
                </button>
            </form>
            <button id="close-edit-modal" 
                class="mt-4 w-full btn-animate text-white px-4 py-2 rounded-md hover:bg-[#3A0000]" 
                style="background-color: #4B0000;">
                Close
            </button>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#user-table').DataTable();

            const $userModal = $('#user-modal');
            const $editUserModal = $('#edit-user-modal');

            // Modal open/close with fade animations
            function showModal($modal) {
                $modal.removeClass('hidden modal-hide').addClass('modal-show');
            }

            function hideModal($modal) {
                $modal.removeClass('modal-show').addClass('modal-hide');
                setTimeout(() => {
                    $modal.addClass('hidden');
                }, 300); // match animation duration
            }

            $('#open-modal').click(function () {
                showModal($userModal);
            });

            $('#close-modal').click(function () {
                hideModal($userModal);
            });

            $userModal.click(function (e) {
                if (e.target === this) {
                    hideModal($userModal);
                }
            });

            $('.edit-user-btn').click(function () {
                const user = $(this).data('user');
                $('#edit-id').val(user.id);
                $('#edit-first-name').val(user.first_name);
                $('#edit-last-name').val(user.last_name);
                $('#edit-rfid').val(user.rfid_number);
                $('#edit-email').val(user.email);
                showModal($editUserModal);
            });

            $('#close-edit-modal').click(function () {
                hideModal($editUserModal);
            });

            $editUserModal.click(function (e) {
                if (e.target === this) {
                    hideModal($editUserModal);
                }
            });
        });
    </script>

</body>

</html>
