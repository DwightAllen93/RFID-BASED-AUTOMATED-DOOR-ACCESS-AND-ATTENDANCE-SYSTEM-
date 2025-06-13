<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
include('config.php');
$user_id = $_SESSION['user_logged_in'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- DataTables Buttons -->
    <link href="./css/buttons.dataTables.min.css" rel="stylesheet" />
    <script src="./js/dataTables.buttons.min.js"></script>
    <script src="./js/buttons.html5.min.js"></script>
    <script src="./js/buttons.print.min.js"></script>
    <script src="./js/jszip.min.js"></script>
    <script src="./js/pdfmake.min.js"></script>
    <script src="./js/vfs_fonts.js"></script>

    <style>
        /* fadeInUp animation */
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

        /* Button hover scale effect */
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
            from {opacity: 0;}
            to {opacity: 1;}
        }
        @keyframes modalFadeOut {
            from {opacity: 1;}
            to {opacity: 0;}
        }
    </style>
</head>

<body class="bg-gray-100 font-sans relative">

    <!-- Background image div -->
    <div
        class="absolute inset-0 -z-20 bg-cover bg-center"
        style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')"
    ></div>

    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-10 min-h-screen relative z-10 fade-in-up">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Attendance Viewer</h1>

            <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-4 mb-6">
                <!-- form fields here, same as before -->
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1">Select Subject</label>
                    <select id="subject_id" name="subject_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="" disabled selected>Choose subject</option>
                        <?php
                        $subjectQuery = "SELECT id, subject_name FROM subject WHERE status = 0";
                        $subjectResult = mysqli_query($conn, $subjectQuery);
                        while ($subject = mysqli_fetch_assoc($subjectResult)) {
                            $selected = (isset($_GET['subject_id']) && $_GET['subject_id'] == $subject['id']) ? 'selected' : '';
                            echo "<option value='{$subject['id']}' $selected>{$subject['subject_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                    <input type="date" id="date" name="date" required
                        value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Select Section</label>
                    <select id="section" name="section"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="" selected>All Sections</option>
                        <?php
                        $sections = ['1-A', '1-B', '2-A', '2-B', '3-A', '3-B', '4-A', '4-B', '5-A', '5-B'];
                        foreach ($sections as $sec) {
                            $selected = (isset($_GET['section']) && $_GET['section'] == $sec) ? 'selected' : '';
                            echo "<option value='$sec' $selected>$sec</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <button type="submit" 
                        class="btn-animate text-white px-6 py-2 rounded-md hover:bg-[#3A0000]" 
                        style="background-color: #4B0000;">
                        View
                    </button>
                </div>
            </form>

            <!-- Add Attendance Button -->
            <button id="openAddModal" 
                class="btn-animate text-white px-4 py-2 rounded-md hover:bg-[#3A0000] mb-4" 
                style="background-color: #4B0000;">
                <span style="color: gold;">➕</span>&nbsp;Add Attendance
            </button>

            <?php
            if (isset($_GET['subject_id']) && isset($_GET['date'])) {
                $subject_id = intval($_GET['subject_id']);
                $date = $_GET['date'];

                $section_filter = $_GET['section'] ?? '';
                if ($section_filter) {
                    $sql = "SELECT a.id, a.rfid, s.first_name, s.last_name, s.section, a.timestamp
                            FROM attendance a
                            JOIN student s ON a.user_id = s.id
                            WHERE a.subject = ? AND DATE(a.timestamp) = ? AND s.section = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $subject_id, $date, $section_filter);
                } else {
                    $sql = "SELECT a.id, a.rfid, s.first_name, s.last_name, s.section, a.timestamp
                            FROM attendance a
                            JOIN student s ON a.user_id = s.id
                            WHERE a.subject = ? AND DATE(a.timestamp) = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $subject_id, $date);
                }

                $stmt->execute();
                $result = $stmt->get_result();
                ?>

                <table id="attendanceTable" class="display w-full mt-6">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>RFID</th>
                            <th>Name</th>
                            <th>Section</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $seen = [];
                        while ($row = $result->fetch_assoc()):
                            $name = $row['first_name'] . ' ' . $row['last_name'];
                            $key = $row['timestamp'] . '_' . $name . '_' . $row['rfid'];
                            if (isset($seen[$key])) continue;
                            $seen[$key] = true;
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['rfid']; ?></td>
                            <td><?php echo htmlspecialchars($name); ?></td>
                            <td><?php echo htmlspecialchars($row['section']); ?></td>
                            <td><?php echo $row['timestamp']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            <?php } ?>
        </div>
    </div>

    <?php if (isset($_SESSION['attendance_error'])): ?>
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 border border-red-400 rounded">
            <?php
            echo $_SESSION['attendance_error'];
            unset($_SESSION['attendance_error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['attendance_success'])): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 border border-green-400 rounded">
            <?php
            echo $_SESSION['attendance_success'];
            unset($_SESSION['attendance_success']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Add Attendance Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md relative modal-content">
            <button id="closeAddModal"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl">&times;</button>
            <h2 class="text-xl font-bold mb-4">Add Attendance</h2>
            <form action="add_attendance.php" method="POST" class="space-y-4">
                <div>
                    <label for="modal_subject_id" class="block text-sm font-medium text-gray-700">Select Subject</label>
                    <select id="modal_subject_id" name="subject_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="" disabled selected>Choose subject</option>
                        <?php
                        $subjectQuery = "SELECT id, subject_name FROM subject WHERE status = 0";
                        $subjectResult = mysqli_query($conn, $subjectQuery);
                        while ($subject = mysqli_fetch_assoc($subjectResult)) {
                            $selected = (isset($_GET['subject_id']) && $_GET['subject_id'] == $subject['id']) ? 'selected' : '';
                            echo "<option value='{$subject['id']}' $selected>{$subject['subject_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="rfid" class="block text-sm font-medium text-gray-700">RFID Number</label>
                    <input type="text" name="rfid" id="rfid" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                </div>
                <div>
                    <label for="timestamp" class="block text-sm font-medium text-gray-700">Timestamp</label>
                    <input type="datetime-local" name="timestamp" id="timestamp" required
                        value="<?php echo date('Y-m-d\TH:i'); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md" />
                </div>
                <button type="submit" 
                    class="w-full btn-animate text-white px-4 py-2 rounded-md hover:bg-[#3A0000]" 
                    style="background-color: #4B0000;">
                    Submit Attendance
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        $(document).ready(function () {
            const table = $('#attendanceTable').DataTable({
                dom: 'Blfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '⬇️ Download Excel',
                        className: 'bg-green-500 text-white px-4 py-6 rounded-md hover:bg-green-600'
                    }
                ]
            });

            // Modal open/close with fade animation
            const $modal = $('#addModal');
            const $modalContent = $modal.find('.modal-content');

            $('#openAddModal').click(function () {
                $modal.removeClass('hidden modal-hide').addClass('modal-show');
            });

            $('#closeAddModal').click(function () {
                $modal.removeClass('modal-show').addClass('modal-hide');
                setTimeout(() => {
                    $modal.addClass('hidden');
                }, 300); // match animation duration
            });

            $modal.click(function (e) {
                if (e.target === this) {
                    $modal.removeClass('modal-show').addClass('modal-hide');
                    setTimeout(() => {
                        $modal.addClass('hidden');
                    }, 300);
                }
            });
        });
    </script>

</body>

</html>
