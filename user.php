<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Include database connection
include('config.php');

// Handle user removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_user'])) {
    $user_id = $_POST['user_id'];
    $sql_update = "UPDATE users SET status = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
    exit();
}

// Handle adding a new student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $section = $_POST['section'];
    $school_year = $_POST['school_year'];
    $rfid_number = isset($_POST['rfid_number']) ? $_POST['rfid_number'] : null;

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($section) || empty($school_year)) {
        echo "<script>showAlert('Duplicate RFID number. Please use a unique RFID.', false);</script>";
    } else {
        $sql_insert = "INSERT INTO users (first_name, last_name, email, section, school_year, rfid_number, status) 
                       VALUES (?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param('ssssss', $first_name, $last_name, $email, $section, $school_year, $rfid_number);

        if ($stmt->execute()) {
            echo "<script>showAlert('Student added successfully!', true);</script>";
        } else {
            echo "<script>showAlert('Failed to add student: " . $stmt->error . "', false);</script>";
        }
        $stmt->close();
    }
}

// Fetch active users
$sql_users = "SELECT * FROM users WHERE status != 1";
$result_users = $conn->query($sql_users);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">
    <?php include('navbar.php'); ?>

    <div class="min-h-screen flex flex-col items-center justify-center py-10">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-6xl">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Manage Users</h1>
<!-- Alert Modal -->
<div id="alert-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="relative bg-white p-6 rounded-lg shadow-lg w-full max-w-md mx-4 md:mx-auto overflow-hidden">
        <div id="alert-message" class="text-center text-lg text-gray-800"></div>
    </div>
</div>

<button id="open-add-student-modal" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Add Student</button>


            <table id="user-table" class="min-w-full table-auto border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">ID</th>
                        <th class="border px-4 py-2">First Name</th>
                        <th class="border px-4 py-2">Last Name</th>
                        <th class="border px-4 py-2">RFID Number</th>
                        <th class="border px-4 py-2">Email</th>
                        <th class="border px-4 py-2">Section</th>
                        <th class="border px-4 py-2">School Year</th>
                        <th class="border px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
    <?php while ($user = $result_users->fetch_assoc()) : ?>
        <?php if ($user['email'] === 'admindev@gmail.com') continue; // Skip rendering this row ?>
        <tr data-user-id="<?php echo $user['id']; ?>">
            <td class="border px-4 py-2"><?php echo $user['id']; ?></td>
            <td class="border px-4 py-2"><?php echo $user['first_name']; ?></td>
            <td class="border px-4 py-2"><?php echo $user['last_name']; ?></td>
            <td class="border px-4 py-2"><?php echo $user['rfid_number']; ?></td>
            <td class="border px-4 py-2"><?php echo $user['email']; ?></td>
            <td class="border px-4 py-2"><?php echo $user['section']; ?></td>
            <td class="border px-4 py-2"><?php echo $user['school_year']; ?></td>
            <td class="border px-4 py-2">
                <button class="remove-user bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Remove</button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

            </table>
        </div>
    </div>

<!-- Add Student Modal -->
<div id="add-student-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="relative bg-white p-6 rounded-lg shadow-lg w-full max-w-md mx-4 md:mx-auto overflow-hidden">
        <!-- Close Button -->
        <button id="close-add-student-modal" 
            class="absolute top-3 right-3 text-gray-600 hover:text-red-600">
            &#x2715;
        </button>

        <h2 class="text-2xl font-bold mb-4 text-center">Add Student</h2>
        <form id="add-student-form" method="POST" action="">

            <div class="mb-4">
                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" id="first_name" name="first_name" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" id="last_name" name="last_name" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                <input type="text" id="section" name="section" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="school_year" class="block text-sm font-medium text-gray-700">School Year</label>
                <input type="text" id="school_year" name="school_year" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <button id="scan-rfid" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600" onclick="startScan()">Scan RFID</button>
                <button id="reset-rfid" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 hidden" onclick="resetScan()">Reset</button>
                <div id="rfid-display" class="mt-4 text-lg text-gray-800 font-mono bg-gray-200 p-2 rounded-md"></div>
                <input type="hidden" id="rfid_number" name="rfid_number"> <!-- Hidden field to capture the RFID -->
            </div>

            <button type="submit" name="add_student" class="w-full px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Add Student</button>
        </form>
    </div>
</div>


    <script>
        $(document).ready(function() {
            $('#user-table').DataTable();
            $('.remove-user').click(function() {
                const row = $(this).closest('tr');
                const userId = row.data('user-id');
                if (confirm('Are you sure you want to remove this user?')) {
                    $.post('', { remove_user: true, user_id: userId }, function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            alert('User removed successfully!');
                            row.hide(); 
                        } else {
                            alert('Failed to remove user: ' + result.error);
                        }
                    });
                }
            });
        });
        document.getElementById('open-add-student-modal').addEventListener('click', function() {
    document.getElementById('add-student-modal').classList.remove('hidden');
});
document.getElementById('close-add-student-modal').addEventListener('click', function() {
    document.getElementById('add-student-modal').classList.add('hidden');
});

let scannedRFID = ""; // To store the scanned RFID value
let isScanned = false; // Track if an RFID has been scanned

function startScan() {
    const scanButton = document.getElementById('scan-rfid');
    const rfidDisplay = document.getElementById('rfid-display');
    const resetButton = document.getElementById('reset-rfid');
    scannedRFID = ""; // Reset RFID
    rfidDisplay.textContent = "Scanning...";

    if (isScanned) {
        alert("RFID already scanned! Reset to scan again.");
        return;
    }

    // Change button to spinner
    scanButton.innerHTML = `<div class="flex items-center"><svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.372 0 0 5.372 0 12h4zm2 5.291a7.962 7.962 0 01-2-5.291H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Scanning...</div>`;
    scanButton.disabled = true;

    const keydownHandler = function (event) {
        if (event.key.length === 1 && !event.ctrlKey && !event.altKey) {
            scannedRFID += event.key;
            rfidDisplay.textContent = `Scanned RFID: ${scannedRFID}`;
        }

        if (event.key === 'Enter') {
            // Extract only digits
            const cleanRFID = scannedRFID.replace(/\D/g, "");
            console.log("Final Scanned RFID:", cleanRFID);

            // Show scanned RFID and disable further scanning
            if (cleanRFID) {
                isScanned = true;
                scanButton.innerHTML = "Scan RFID";
                scanButton.disabled = false;
                resetButton.classList.remove('hidden'); // Show reset button
                rfidDisplay.textContent = `Scanned RFID: ${cleanRFID}`;
                document.getElementById('rfid_number').value = cleanRFID; // Set RFID to the hidden input
            } else {
                alert("Invalid RFID scanned. Try again.");
                scanButton.innerHTML = "Scan RFID";
                scanButton.disabled = false;
            }

            // Remove event listener
            document.removeEventListener('keydown', keydownHandler);
        }
    };

    document.addEventListener('keydown', keydownHandler);
}

function resetScan() {
    // Reset scan state
    isScanned = false;
    scannedRFID = "";
    document.getElementById('rfid-display').textContent = "RFID reset. Ready to scan.";
    document.getElementById('reset-rfid').classList.add('hidden'); // Hide reset button
}

function showAlert(message, isSuccess = true) {
    const alertModal = document.getElementById('alert-modal');
    const alertMessage = document.getElementById('alert-message');

    // Set message and style based on success or error
    alertMessage.textContent = message;
    alertMessage.classList.remove('text-red-500', 'text-green-500');
    alertMessage.classList.add(isSuccess ? 'text-green-500' : 'text-red-500');

    // Display the modal
    alertModal.classList.remove('hidden');

    // Hide the alert modal after 3 seconds
    setTimeout(function() {
        alertModal.classList.add('hidden');
    }, 3000);
}

// Show alert when a user is removed
$('.remove-user').click(function() {
    const row = $(this).closest('tr');
    const userId = row.data('user-id');
    if (confirm('Are you sure you want to remove this user?')) {
        $.post('', { remove_user: true, user_id: userId }, function(response) {
            const result = JSON.parse(response);
            if (result.success) {
                showAlert('User removed successfully!', true);
                row.hide(); 
            } else {
                showAlert('Failed to remove user: ' + result.error, false);
            }
        });
    }
});

// Show alert when a student is added successfully or fails



    </script>
</body>

</html>
