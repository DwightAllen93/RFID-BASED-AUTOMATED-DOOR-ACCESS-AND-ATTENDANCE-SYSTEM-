<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include('config.php');

if (!isset($_GET['subject_id'])) {
    echo "Subject ID is required.";
    exit();
}

$subject_id = intval($_GET['subject_id']);
$filter_section = isset($_GET['filter_section']) ? trim($_GET['filter_section']) : "";

// Fetch subject info
$sql_subject = "SELECT * FROM subject WHERE id = ?";
$stmt_sub = $conn->prepare($sql_subject);
$stmt_sub->bind_param("i", $subject_id);
$stmt_sub->execute();
$subject = $stmt_sub->get_result()->fetch_assoc();

if (!$subject) {
    echo "Subject not found.";
    exit();
}

// ✅ Build query for student filtering
$base_query = "SELECT * FROM student WHERE FIND_IN_SET(?, subject_ids) > 0 AND status = 0";
$params = [$subject_id];
$types = "s";

if (!empty($filter_section)) {
    $base_query .= " AND section = ?";
    $params[] = $filter_section;
    $types .= "s";
}

$base_query .= " ORDER BY last_name, first_name";
$stmt = $conn->prepare($base_query);

if (count($params) === 1) {
    $stmt->bind_param($types, $params[0]);
} elseif (count($params) === 2) {
    $stmt->bind_param($types, $params[0], $params[1]);
}

$stmt->execute();
$students = $stmt->get_result();

// ✅ Fetch schedule list for this subject
$sql_schedule = "
    SELECT s.id AS schedule_id, s.day, s.time, s.end_time, s.door, s.section,
           GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') AS instructors
    FROM schedule s
    LEFT JOIN student_schedule ss ON s.id = ss.schedule_id AND ss.status = 0
    LEFT JOIN users u ON ss.users_id = u.id AND u.status = 0
    WHERE s.subject_id = ? AND s.status != 3
    GROUP BY s.id
    ORDER BY FIELD(s.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), s.time
";
$stmt_schedule = $conn->prepare($sql_schedule);
$stmt_schedule->bind_param("i", $subject_id);
$stmt_schedule->execute();
$schedules = $stmt_schedule->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Students Enrolled in <?= htmlspecialchars($subject['subject_name']) ?></title>
    <link rel="icon" type="image/x-icon" href="./img/l.png" />
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 font-sans">
<?php include('navbar.php'); ?>

<div class="ml-64 p-10 min-h-screen max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Students Enrolled in: <?= htmlspecialchars($subject['subject_name']) ?></h1>

    <?php if ($schedules->num_rows > 0): ?>
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Class Schedule:</h2>
        <ul class="list-disc list-inside">
            <?php while ($sched = $schedules->fetch_assoc()): ?>
                <?php 
                    $start = date("g:i A", strtotime($sched['time']));
                    $end = date("g:i A", strtotime($sched['end_time']));
                    $instructors = $sched['instructors'] ?: 'No instructor assigned';
                ?>
                <li>
                    <?= htmlspecialchars($sched['day']) ?>: <?= $start ?> - <?= $end ?> 
                    (Room <?= htmlspecialchars($sched['door']) ?> | Section <?= htmlspecialchars($sched['section']) ?>)
                    <br><span class="text-gray-600 text-sm">Instructor(s): <?= htmlspecialchars($instructors) ?></span>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php else: ?>
        <p class="mb-6 text-gray-600">No schedule available for this subject.</p>
    <?php endif; ?>

    <!-- 🔵 Filter Form -->
    <form method="GET" class="mb-4 flex items-center gap-4">
        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">

        <label for="filter_section" class="text-sm font-medium">Filter by Section:</label>
        <select id="filter_section" name="filter_section" class="border rounded px-2 py-1">
            <option value="">All Sections</option>
            <?php
            $sections = ['1-A','1-B','2-A','2-B','3-A','3-B','4-A','4-B','5-A','5-B'];
            foreach ($sections as $section) {
                $selected = ($filter_section === $section) ? 'selected' : '';
                echo "<option value=\"$section\" $selected>$section</option>";
            }
            ?>
        </select>

        <button type="submit" class="text-white px-3 py-1 rounded hover:bg-[#3A0000]" style="background-color: #4B0000;"> Apply </button>
    </form>

    <div class="bg-white rounded-lg shadow p-6">
        <table id="studentsTable" class="display w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>RFID Number</th>
                    <th>Section</th>
                    <th>School Year</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($student['id']) ?></td>
                    <td><?= htmlspecialchars($student['first_name']) ?></td>
                    <td><?= htmlspecialchars($student['last_name']) ?></td>
                    <td><?= htmlspecialchars($student['rfid_number']) ?></td>
                    <td><?= htmlspecialchars($student['section']) ?></td>
                    <td><?= htmlspecialchars($student['school_year']) ?></td>
                    <td><?= $student['status'] == 0 ? 'Active' : 'Inactive' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mt-6">
            <a href="managesubject.php?id=<?= $subject_id ?>" class="text-[#4B0000] hover:underline">&larr; Back to Manage Subject</a>  
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#studentsTable').DataTable();
    });
</script>
    <!-- Background image div inserted here -->
    <div class="absolute inset-0 -z-20 bg-cover bg-center" style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')"></div>
</body>
</html>
