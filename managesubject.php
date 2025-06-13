<?php

session_start();


if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}


include('config.php');


if (isset($_GET['id'])) {
    $subject_id = $_GET['id'];

    // Get the subject details
    $sql_subject = "SELECT * FROM subject WHERE id = $subject_id";
    $result_subject = mysqli_query($conn, $sql_subject);
    $subject = mysqli_fetch_assoc($result_subject);

    // Get schedules with assigned students
$sql_schedule = "
    SELECT 
        s.id AS schedule_id,
        s.day,
        s.time,
        s.end_time,
        s.section,  -- ✅ ADD THIS LINE
        s.status,
        s.door,
        s.created_at,
        s.updated_at,
        u.first_name AS student_name
    FROM 
        schedule s
    LEFT JOIN 
        student_schedule ss ON s.id = ss.schedule_id
    LEFT JOIN 
        users u ON ss.users_id = u.id
    WHERE 
        s.subject_id = $subject_id 
        AND s.status != 3
    ORDER BY 
        s.id ASC
";

    $result_schedule = mysqli_query($conn, $sql_schedule);
} else {
    echo "Subject ID not found.";
    exit();
}


// ADD schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $subject_id = $_GET['id'];
    $day = $_POST['day'];
    $time = $_POST['time'];
    $end_time = $_POST['end_time'];
    $door = $_POST['door'];
    $section = $_POST['section']; // 🔹 new line
    $status = 0;
    $state = 0;

    $sql_insert = "INSERT INTO schedule (subject_id, day, time, end_time, section, status, created_at, updated_at, door, state) 
                   VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)";

    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param('isssssii', $subject_id, $day, $time, $end_time, $section, $status, $door, $state); // 🔹 updated

    if ($stmt->execute()) {
        header("Location: managesubject.php?id=$subject_id&status=added");
        exit();
    } else {
        echo "Error adding schedule: " . $stmt->error;
    }
    $stmt->close();
}



// TOGGLE schedule status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $schedule_id = $_POST['schedule_id'];
    $current_status = $_POST['current_status'];
    $new_status = $current_status == 1 ? 0 : 1;

    $update_query = "UPDATE schedule SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ii', $new_status, $schedule_id);

    if ($stmt->execute()) {
        header("Location: managesubject.php?id=$subject_id&status=success");
        exit();
    } else {
        header("Location: managesubject.php?id=$subject_id&status=error");
        exit();
    }
    $stmt->close();
}

// REMOVE schedule (set status to 3)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_schedule'])) {
    $schedule_id = $_POST['schedule_id'];

    $update_query = "UPDATE schedule SET status = 3, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('i', $schedule_id);

    if ($stmt->execute()) {
        header("Location: managesubject.php?id=$subject_id&status=removed");
        exit();
    } else {
        header("Location: managesubject.php?id=$subject_id&status=error");
        exit();
    }
    $stmt->close();
}

// Alert messages
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            echo "<script>alert('Schedule status updated successfully!');</script>";
            break;
        case 'error':
            echo "<script>alert('Failed to update schedule status.');</script>";
            break;
        case 'removed':
            echo "<script>alert('Schedule removed successfully!');</script>";
            break;
        case 'added':
            echo "<script>alert('Schedule added successfully!');</script>";
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_schedule'])) {
    $schedule_id = $_POST['edit_schedule_id'];
    $new_day = $_POST['edit_day'];
    $new_time = $_POST['edit_time'];
    $new_end_time = $_POST['edit_end_time'];  // added for end_time

    $update_query = "UPDATE schedule SET day = ?, time = ?, end_time = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('sssi', $new_day, $new_time, $new_end_time, $schedule_id);

    if ($stmt->execute()) {
        header("Location: managesubject.php?id=$subject_id&status=success");
        exit();
    } else {
        echo "<script>alert('Failed to update schedule.');</script>";
    }

    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subject</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png">
    <script src="./css/3.4.16"></script>
</head>

<body class="bg-gray-100 font-sans">
    <?php include('navbar.php'); ?>

    <div class="min-h-screen flex flex-col items-center justify-center py-10">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Manage Subject: <?php echo $subject['subject_name']; ?></h1>

            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Subject Details</h2>
                <table class="min-w-full table-auto">
                    <tr>
                        <td class="border px-4 py-2 font-medium">Subject Name:</td>
                        <td class="border px-4 py-2"><?php echo $subject['subject_name']; ?></td>
                    </tr>
                    <tr>
                        <td class="border px-4 py-2 font-medium">Status:</td>
                        <td class="border px-4 py-2"><?php echo ($subject['status'] == 1 ? 'Inactive' : 'Active'); ?></td>
                    </tr>
                    <tr>
                        <td class="border px-4 py-2 font-medium">Created By:</td>
                        <td class="border px-4 py-2">
                            <?php
                          
                            $created_user_id = $subject['created_user'];
                            $sql_user = "SELECT first_name, last_name FROM users WHERE id = $created_user_id";
                            $result_user = mysqli_query($conn, $sql_user);
                            $user = mysqli_fetch_assoc($result_user);
                            echo $user['first_name'] . " " . $user['last_name'];
                            ?>
                        </td>
                    </tr>
                </table>
            </div>

        
            <button id="open-modal" class="text-white px-4 py-2 rounded-md mb-6 hover:bg-red-900 transition" style="background-color: #4B0000;">
                Add Schedule
            </button>

       
         <a href="viewstudent.php?subject_id=<?php echo $subject_id; ?>">

    <button class="text-white px-4 py-2 rounded-md mb-6 hover:bg-red-900 transition" style="background-color: #4B0000;">
                View Student
            </button>
</a>

<button id="openModal" class="text-white px-4 py-2 rounded-md mb-6 hover:bg-red-900 transition" style="background-color: #4B0000;">
    Assign  Schedule
</button>


<h2 class="text-xl font-semibold mb-4">Schedule</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $currentSchedule = null;
    while ($schedule = mysqli_fetch_assoc($result_schedule)) {
        if ($currentSchedule !== $schedule['schedule_id']) {
            if ($currentSchedule !== null) {
                echo "</ul>";
                echo "</div>";
            }

            echo "<div class='bg-white border border-gray-300 p-4 rounded-lg shadow-md'>";
            $room_names = [
                '1' => 'Room 115',
                '2' => 'Room 116',
                '3' => 'Room 215',
                '4' => 'Room 216',
                '5' => 'Room 217'
            ];

            $room_display = isset($room_names[$schedule['door']]) ? $room_names[$schedule['door']] : "Room " . $schedule['door'];
            echo "<h3 class='text-lg font-semibold mb-2'>" . $room_display . "</h3>";
            echo "<p class='text-sm'><strong>Day:</strong> " . $schedule['day'] . "</p>";
            echo "<p class='text-sm'><strong>Time:</strong> " . date("h:i A", strtotime($schedule['time'])) . " - " . date("h:i A", strtotime($schedule['end_time'])) . "</p>";

            echo "<p class='text-sm'><strong>Status:</strong> " . ($schedule['status'] == 1 ? 'Inactive' : 'Active') . "</p>";
            echo "<p class='text-sm'><strong>Section:</strong> " . htmlspecialchars($schedule['section']) . "</p>";

            echo "<p class='text-sm text-gray-600 mt-2'><strong>Created At:</strong> " . $schedule['created_at'] . "</p>";
            echo "<p class='text-sm text-gray-600'><strong>Updated At:</strong> " . $schedule['updated_at'] . "</p>";

            echo "<h4 class='text-sm font-semibold mt-4'>Assigned Instructor:</h4>";
            if ($schedule['student_name']) {
                echo "<p class='text-sm text-gray-800'>" . $schedule['student_name'] . "</p>";
            } else {
                echo "<p class='text-sm text-gray-500'>No instructor assigned</p>";
            }

            echo "<ul class='list-disc list-inside text-sm'>";
            $currentSchedule = $schedule['schedule_id'];
        }

        echo "<div class='mt-4'>";
        echo "<form method='POST' action='' class='inline-block'>";
        echo "<input type='hidden' name='schedule_id' value='" . $schedule['schedule_id'] . "'>";
        echo "<input type='hidden' name='current_status' value='" . $schedule['status'] . "'>";
        echo "<button type='submit' name='toggle_status' class='py-1 px-3 rounded-lg " .
            ($schedule['status'] == 1 ? "bg-green-500 text-white hover:bg-green-700" : "bg-red-500 text-white hover:bg-red-700") . "'>";
        echo $schedule['status'] == 1 ? 'Activate' : 'Deactivate';
        echo "</button>";
        echo "</form>";

        // Remove button
        echo "<form method='POST' action='' class='inline-block ml-2'>";
        echo "<input type='hidden' name='schedule_id' value='" . $schedule['schedule_id'] . "'>";
        echo "<button type='submit' name='remove_schedule' class='py-1 px-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-700'>";
        echo "Remove";
        echo "</button>";
        echo "</form>";

        // Edit button
        echo "<form method='POST' action='' class='inline-block ml-2'>";
        echo "<button type='button' class='py-1 px-3 bg-blue-500 text-white rounded-lg hover:bg-blue-700' ";
        echo "onclick=\"openEditModal('" . $schedule['schedule_id'] . "', '" . $schedule['day'] . "', '" . $schedule['time'] . "', '" . $schedule['end_time'] . "')\">";
        echo "Edit";
        echo "</button>";
        echo "</form>";

        echo "</div>";
    }

    echo "</ul>";
    echo "</div>";
    ?>
</div>

<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div class="bg-white p-6 rounded-md w-96">
        <h2 class="text-xl font-bold mb-4">Edit Schedule</h2>
        <form method="POST" action="">
            <input type="hidden" name="edit_schedule_id" id="edit_schedule_id">

            <!-- Day -->
            <div class="mb-4">
                <label for="edit_day" class="block text-sm font-medium text-gray-700">Day</label>
                <select name="edit_day" id="edit_day" required class="w-full border px-2 py-1 rounded">
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>
            </div>

            <!-- Start Time -->
            <div class="mb-4">
                <label for="edit_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                <input type="time" name="edit_time" id="edit_time" required class="w-full border px-2 py-1 rounded">
            </div>

            <!-- End Time -->
            <div class="mb-4">
                <label for="edit_end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                <input type="time" name="edit_end_time" id="edit_end_time" required class="w-full border px-2 py-1 rounded">
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="closeEditModal()" class="mr-2 bg-gray-400 text-white px-3 py-1 rounded">Cancel</button>
                <button type="submit" name="update_schedule" class="bg-blue-600 text-white px-4 py-1 rounded">Update</button>
            </div>
        </form>
    </div>
</div>





            <div class="mt-6">
                <a href="dashboard.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600">Back to Dashboard</a>
            </div>
        </div>
    </div>


    <div id="schedule-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg w-96">
        <h2 class="text-2xl font-bold mb-4">Add Schedule</h2>
        <form method="POST" action="">
            <!-- Day -->
            <div class="mb-4">
                <label for="day" class="block text-sm font-medium text-gray-700">Day</label>
                <select id="day" name="day" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="" disabled selected>Select a day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>
            </div>

<div class="mb-4">
    <label for="time" class="block text-sm font-medium text-gray-700">Start Time</label>
    <select id="time" name="time" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <option value="" disabled selected>Select a time</option>
        <option value="08:00:00">08:00 am</option>
        <option value="08:30:00">08:30 am</option>
        <option value="09:00:00">09:00 am</option>
        <option value="09:30:00">09:30 am</option>
        <option value="10:00:00">10:00 am</option>
        <option value="10:30:00">10:30 am</option>
        <option value="11:00:00">11:00 am</option>
        <option value="11:30:00">11:30 am</option>
        <option value="12:00:00">12:00 pm</option>
        <option value="12:30:00">12:30 pm</option>
        <option value="13:00:00">1:00 pm</option>
        <option value="13:30:00">1:30 pm</option>
        <option value="14:00:00">2:00 pm</option>
        <option value="14:30:00">2:30 pm</option>
        <option value="15:00:00">3:00 pm</option>
        <option value="15:30:00">3:30 pm</option>
        <option value="16:00:00">4:00 pm</option>
        <option value="16:30:00">4:30 pm</option>
        <option value="17:00:00">5:00 pm</option>
        <option value="17:30:00">5:30 pm</option>
        <option value="18:00:00">6:00 pm</option>
    </select>
</div>

<!-- End Time -->
<div class="mb-4">
    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
    <select id="end_time" name="end_time" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <option value="" disabled selected>Select an end time</option>
        <option value="08:30:00">08:30 am</option>
        <option value="09:00:00">09:00 am</option>
        <option value="09:30:00">09:30 am</option>
        <option value="10:00:00">10:00 am</option>
        <option value="10:30:00">10:30 am</option>
        <option value="11:00:00">11:00 am</option>
        <option value="11:30:00">11:30 am</option>
        <option value="12:00:00">12:00 pm</option>
        <option value="12:30:00">12:30 pm</option>
        <option value="13:00:00">1:00 pm</option>
        <option value="13:30:00">1:30 pm</option>
        <option value="14:00:00">2:00 pm</option>
        <option value="14:30:00">2:30 pm</option>
        <option value="15:00:00">3:00 pm</option>
        <option value="15:30:00">3:30 pm</option>
        <option value="16:00:00">4:00 pm</option>
        <option value="16:30:00">4:30 pm</option>
        <option value="17:00:00">5:00 pm</option>
        <option value="17:30:00">5:30 pm</option>
        <option value="18:00:00">6:00 pm</option>
    </select>
</div>



            <!-- Room -->
            <div class="mb-4">
    <label for="door" class="block text-sm font-medium text-gray-700">Room (Door)</label>
    <select id="door" name="door" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <option value="" disabled selected>Select a room</option>
        <option value="1">Room 115</option>
        <option value="2">Room 116</option>
        <option value="3">Room 215</option>
        <option value="4">Room 216</option>
        <option value="5">Room 217</option>
    </select>
</div>

<!-- Section -->
<div class="mb-4">
    <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
    <select id="section" name="section" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <option value="" disabled selected>Select a section</option>
        <?php
        $grades = [1, 2, 3, 4, 5];
        foreach ($grades as $grade) {
            echo "<option value='{$grade}-A'>{$grade}-A</option>";
            echo "<option value='{$grade}-B'>{$grade}-B</option>";
        }
        ?>
    </select>
</div>

            <button type="submit" name="add_schedule" class="w-full px-4 py-2 rounded-md mb-6 hover:bg-red-900 text-white transition" style="background-color: #4B0000;">Save Schedule</button>
        </form>
        <button id="close-modal" class="mt-4 w-full px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Close</button>
    </div>
</div>



    
<div id="assignScheduleModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-1/3">
        <div class="p-4 border-b">
            <h2 class="text-xl font-semibold">Assign  Schedule</h2>
            <button id="closeModal" class="text-gray-500 hover:text-gray-700 float-right">✖</button>
        </div>
        <div class="p-4">
            <form id="assignScheduleForm" method="POST" action="assign_schedule.php">
                <div class="mb-4">
                <input type="hidden" name="subject_id" value="<?= $subject_id; ?>">

                    <label for="userSelect" class="block text-sm font-medium text-gray-700">Select Instructor</label>
                    <select class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" id="userSelect" name="users_id" required>
                        <option value="">Select Instructor</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="scheduleSelect" class="block text-sm font-medium text-gray-700">Select Schedule</label>
                    <select class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" id="scheduleSelect" name="schedule_id" required>
                        <option value="">Select Schedule</option>
                    </select>
                </div>
                
                <button type="submit" class="text-white px-4 py-2 rounded-md mb-6 hover:bg-red-900 transition" style="background-color: #4B0000;">
                Confirm
                </button>
            </form>
        </div>
    </div>
</div>

    <script>
        document.getElementById('open-modal').addEventListener('click', function() {
            document.getElementById('schedule-modal').classList.remove('hidden');
        });

        document.getElementById('close-modal').addEventListener('click', function() {
            document.getElementById('schedule-modal').classList.add('hidden');
        });

const openModal = document.getElementById('openModal');
const closeModal = document.getElementById('closeModal');
const modal = document.getElementById('assignScheduleModal');

openModal.addEventListener('click', () => {
    modal.classList.remove('hidden');
});

closeModal.addEventListener('click', () => {
    modal.classList.add('hidden');
});


fetch('fetch_users.php')
    .then(response => response.json())
    .then(data => {
        let userSelect = document.getElementById('userSelect');
        data.forEach(user => {
            let option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.first_name + " " + user.last_name;
            userSelect.appendChild(option);
        });
    });

const urlParams = new URLSearchParams(window.location.search);
const subject_id = urlParams.get('id'); 
fetch(`fetch_schedules.php?subject_id=${subject_id}`)
    .then(response => response.json())
    .then(data => {
        let scheduleSelect = document.getElementById('scheduleSelect');
        scheduleSelect.innerHTML = ''; 
        data.forEach(schedule => {
            // Convert 24h time to 12h format
            let timeParts = schedule.time.split(':');
            let hour = parseInt(timeParts[0], 10);
            let minute = timeParts[1];
            let ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12; // convert 0 to 12
            let time12 = `${hour}:${minute} ${ampm}`;

            let option = document.createElement('option');
            option.value = schedule.id;
            let roomNames = {
            '1': '116',
            '2': '115',
            '3': '215',
            '4': '216',
            '5': '217'
        };

        let roomDisplay = roomNames[schedule.door] || schedule.door;

        option.textContent = `${schedule.day} - ${time12} (Room: ${roomDisplay})`;


            scheduleSelect.appendChild(option);
        });
    })
    .catch(error => console.error('Error fetching schedules:', error));

    </script>
    <script>
    function openEditModal(id, day, startTime, endTime) {
    document.getElementById('edit_schedule_id').value = id;
    document.getElementById('edit_day').value = day;
    document.getElementById('edit_time').value = startTime;
    document.getElementById('edit_end_time').value = endTime;
    document.getElementById('edit-modal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
}

</script>

    <div class="absolute inset-0 -z-20 bg-cover bg-center"
    style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')">
  </div>
</body>

</html>
