<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include('config.php');

// Today's Attendance Count
$today = date('Y-m-d');
$attendance_today_result = mysqli_query($conn, "SELECT COUNT(*) AS total_today FROM attendance WHERE DATE(timestamp) = '$today'");
$attendance_today = mysqli_fetch_assoc($attendance_today_result)['total_today'];

// Peak Entry Times (by hour)
$peak_query = "SELECT HOUR(timestamp) AS hour, COUNT(*) AS total FROM attendance GROUP BY hour ORDER BY hour";
$peak_result = mysqli_query($conn, $peak_query);
$peak_data = [];
while ($row = mysqli_fetch_assoc($peak_result)) {
    $peak_data[] = $row;
}

// Weekly Attendance Trends
$last_week = date('Y-m-d', strtotime('-6 days'));
$week_query = "SELECT DATE(timestamp) AS day, COUNT(*) AS total FROM attendance WHERE DATE(timestamp) >= '$last_week' GROUP BY day ORDER BY day";
$week_result = mysqli_query($conn, $week_query);
$weekly_data = [];
while ($row = mysqli_fetch_assoc($week_result)) {
    $weekly_data[] = $row;
}

// Attendance Rate per Subject
$subject_query = "SELECT subject, COUNT(DISTINCT user_id) AS attendees FROM attendance GROUP BY subject";
$subject_result = mysqli_query($conn, $subject_query);
$attendance_per_subject = [];
$enrolled = ['Math' => 25, 'Science' => 20, 'English' => 30]; // You can fetch this dynamically
while ($row = mysqli_fetch_assoc($subject_result)) {
    $subject = $row['subject'];
    $attendees = $row['attendees'];
    $rate = isset($enrolled[$subject]) ? round(($attendees / $enrolled[$subject]) * 100, 1) : 0;
    $attendance_per_subject[] = ['subject' => $subject, 'rate' => $rate];
}



$log_result = mysqli_query($conn, "
    SELECT log.*, subject.subject_name 
    FROM log 
    LEFT JOIN subject ON log.subject_id = subject.id 
    ORDER BY log.timestamp DESC 
    LIMIT 100
");
$student_log_result = mysqli_query($conn, "
    SELECT student_log.*, subject.subject_name 
    FROM student_log 
    LEFT JOIN subject ON student_log.subject_id = subject.id 
    ORDER BY student_log.timestamp DESC 
    LIMIT 100
");

$room_names = [
    1 => '115',
    2 => '116',
    3 => '215',
    4 => '216',
    5 => '217',
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Reports</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png" />
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet" />
    <script src="./js/chart.js"></script>

    <style>
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
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Analytics Dashboard</h1>

            <!-- Attendance Today -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="bg-indigo-100 p-6 rounded-lg shadow text-center">
                    <h2 class="text-xl font-semibold text-black">Today's Attendance</h2>
                    <p class="text-4xl font-bold text-black mt-2"><?= $attendance_today ?></p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-white p-4 rounded-lg shadow md:col-span-2 max-w-full">
                    <h3 class="text-lg font-semibold mb-2">Weekly Attendance Trends</h3>
                    <div class="w-full">
                        <canvas id="weekChart" class="w-full" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <button onclick="showLog('instructor')" 
                        class="btn-animate px-4 py-2 text-white rounded mr-2" 
                        style="background-color: #4B0000;" 
                        onmouseover="this.style.backgroundColor='#3A0000';" 
                        onmouseout="this.style.backgroundColor='#4B0000';">
                    Instructor Log
                </button>

                <button onclick="showLog('student')" 
                        class="btn-animate px-4 py-2 text-white rounded" 
                        style="background-color: #4B0000;" 
                        onmouseover="this.style.backgroundColor='#3A0000';" 
                        onmouseout="this.style.backgroundColor='#4B0000';">
                    Student Log
                </button>
            </div>

            <div id="instructorLog" class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4">Instructor Logs</h2>
                <div class="overflow-x-auto">
                    <table id="instructorLogTable" class="display w-full">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>RFID Number</th>
                                <th>User Name</th>
                                <th>Response</th>
                                <th>Room</th>
                                <th>Subject</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($log_result, 0);
                            $seen = [];
                            while ($log = mysqli_fetch_assoc($log_result)):
                                $key = $log['timestamp'] . '_' . $log['user_name'] . '_' . $log['rfid_number'];
                                if (isset($seen[$key])) continue; // Skip duplicate
                                $seen[$key] = true;
                                $room_display = $room_names[$log['door_id']] ?? $log['door_id'];
                            ?>
                            <tr>
                                <td><?= date('M d, Y h:i A', strtotime($log['timestamp'])) ?></td>
                                <td><?= htmlspecialchars($log['rfid_number']) ?></td>
                                <td><?= htmlspecialchars($log['user_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($log['event_type']) ?></td>
                                <td><?= htmlspecialchars($room_display) ?></td>
                                <td><?= htmlspecialchars($log['subject_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="studentLog" class="bg-white p-6 rounded-lg shadow-lg hidden">
                <h2 class="text-2xl font-bold mb-4">Student Logs</h2>
                <div class="overflow-x-auto">
                    <table id="studentLogTable" class="display w-full">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>RFID Number</th>
                                <th>User Name</th>
                                <th>Event Type</th>
                                <th>Room</th>
                                <th>Subject</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $seen = [];
                            while ($log = mysqli_fetch_assoc($student_log_result)):
                                $key = $log['timestamp'] . '_' . $log['user_name'] . '_' . $log['rfid_number'];
                                if (isset($seen[$key])) continue;
                                $seen[$key] = true;
                                $room_display = $room_names[$log['door_id']] ?? $log['door_id'];
                            ?>
                            <tr>
                                <td><?= date('M d, Y h:i A', strtotime($log['timestamp'])) ?></td>
                                <td><?= htmlspecialchars($log['rfid_number']) ?></td>
                                <td><?= htmlspecialchars($log['user_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($log['event_type']) ?></td>
                                <td><?= htmlspecialchars($room_display) ?></td>
                                <td><?= htmlspecialchars($log['subject_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
    const peakLabels = <?= json_encode(array_column($peak_data, 'hour')) ?>;
    const peakCounts = <?= json_encode(array_column($peak_data, 'total')) ?>;
    const weekLabels = <?= json_encode(array_column($weekly_data, 'day')) ?>;
    const weekCounts = <?= json_encode(array_column($weekly_data, 'total')) ?>;

    new Chart(document.getElementById('weekChart'), {
        type: 'line',
        data: {
            labels: weekLabels,
            datasets: [{
                label: 'Attendance',
                data: weekCounts,
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    function showLog(type) {
        document.getElementById('instructorLog').classList.add('hidden');
        document.getElementById('studentLog').classList.add('hidden');

        if (type === 'instructor') {
            document.getElementById('instructorLog').classList.remove('hidden');
        } else {
            document.getElementById('studentLog').classList.remove('hidden');
        }
    }

    $(document).ready(function () {
        $('#instructorLogTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10
        });
        $('#studentLogTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10
        });
    });
</script>

</body>
</html>
