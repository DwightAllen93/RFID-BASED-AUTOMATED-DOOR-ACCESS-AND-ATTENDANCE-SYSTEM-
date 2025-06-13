<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include('config.php');
$user_id = $_SESSION['user_logged_in'];

// Get filter values from GET parameters (or empty string)
$filter_year = $_GET['school_year'] ?? '';
$filter_section = $_GET['section'] ?? '';

// Build query with filters
$query = "SELECT * FROM student WHERE 1=1";
$params = [];
$types = '';

if ($filter_year !== '') {
    $query .= " AND school_year = ?";
    $params[] = $filter_year;
    $types .= 's';
}

if ($filter_section !== '') {
    $query .= " AND section = ?";
    $params[] = $filter_section;
    $types .= 's';
}

$stmt = $conn->prepare($query);

if ($params) {
    // Bind params dynamically
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$student_result = $stmt->get_result();

// For Section dropdown options (1-A to 5-B)
$sections = [];
for ($grade = 1; $grade <= 5; $grade++) {
    $sections[] = $grade . '-A';
    $sections[] = $grade . '-B';
}

// For School Year dropdown options, generate current and next years
$current_year = (int) date('Y');
$school_years = [
    "{$current_year}-" . ($current_year + 1),
    ($current_year + 1) . "-" . ($current_year + 2)
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png">
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- Custom hover animation for buttons -->
    <style>
        button, .inline-block {
            transition: transform 0.3s ease;
        }

        button:hover, .inline-block:hover {
            transform: scale(1.1); /* Makes the button bigger */
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">

    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-10 min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Student</h1>

            <!-- Filter Form -->
            <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
                <div>
                    <label for="school_year" class="block mb-1 font-medium">Academic Year</label>
                    <select name="school_year" id="school_year" class="border rounded px-3 py-2 w-48">
                        <option value="">All Years</option>
                        <?php foreach ($school_years as $year_option): ?>
                            <option value="<?= htmlspecialchars($year_option) ?>" <?= ($filter_year === $year_option) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year_option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="section" class="block mb-1 font-medium">Section</label>
                    <select name="section" id="section" class="border rounded px-3 py-2 w-48">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $section_option): ?>
                            <option value="<?= htmlspecialchars($section_option) ?>" <?= ($filter_section === $section_option) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section_option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                <button type="submit"
                    class="bg-[#4B0000] text-white px-4 py-2 rounded hover:bg-[#3A0000] transition">
                    Filter
                </button>
                </div>
            </form>

            <!-- Students Table -->
            <div class="overflow-x-auto">
                <table id="studentTable" class="display w-full">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th> <!-- Select All Checkbox -->
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>RFID Number</th>
                            <th>Section</th>
                            <th>School Year</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $student_result->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" class="rowCheckbox" value="<?= htmlspecialchars($row['id']) ?>"></td>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['first_name']) ?></td>
                                <td><?= htmlspecialchars($row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['rfid_number']) ?></td>
                                <td><?= htmlspecialchars($row['section']) ?></td>
                                <td><?= htmlspecialchars($row['school_year']) ?></td>
                                <td>
                                    <button class="view-subjects-btn text-blue-600 hover:underline cursor-pointer" data-student-id="<?= htmlspecialchars($row['id']) ?>">
                                        View Subjects
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Enroll Button -->
            <button id="enrollBtn" 
                class="mt-4 bg-[#4B0000] text-white px-4 py-2 rounded hover:bg-[#3A0000]">
                Enroll Subject
            </button>

            <!-- Enroll Subject Modal -->
            <div id="enrollModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white rounded-lg p-6 w-11/12 max-w-md">
                    <h2 class="text-xl font-bold mb-4">Enroll Selected Students to Subject</h2>
                    <form id="enrollForm">
                        <label for="subjectSelect" class="block mb-1 font-medium">Select Subject</label>
                        <select id="subjectSelect" name="subject_id" class="w-full border rounded px-3 py-2 mb-4" required>
                            <option value="" disabled selected>Choose a subject</option>
                            <?php
                            // Fetch subjects for dropdown
                            $subject_query = "SELECT id, subject_name FROM subject WHERE status = 0";
                            $subject_result = $conn->query($subject_query);
                            while ($subject = $subject_result->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($subject['id']) . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
                            }
                            ?>
                        </select>

                        <div class="flex justify-end gap-2">
                            <button type="button" id="cancelEnroll" class="px-4 py-2 bg-gray-400 rounded hover:bg-gray-500 text-white">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-[#4B0000] rounded hover:bg-[#3A0000] text-white">Enroll</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- View Subjects Modal -->
            <div id="viewSubjectsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-11/12 max-w-md">
                    <h2 class="text-xl font-bold mb-4">Enrolled Subjects</h2>
                    <ul id="subjectsList" class="list-disc list-inside text-gray-700 min-h-[50px]">
                        <!-- Subject names will be inserted here -->
                    </ul>
                    <button id="closeViewSubjects" class="mt-4 w-full px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- JavaScript for handling modals, AJAX, and checkboxes -->
    <script>
        const selectedStudentIds = new Set();

        $(document).ready(function () {
            const table = $('#studentTable').DataTable();

            // Reapply checkboxes after table draw (pagination, search, etc.)
            table.on('draw', function () {
                // Recheck selected checkboxes
                table.rows({ search: 'applied' }).nodes().to$().find('.rowCheckbox').each(function () {
                    const id = $(this).val();
                    $(this).prop('checked', selectedStudentIds.has(id));
                });

                // Update Select All checkbox
                const allChecked = table.rows({ search: 'applied' }).nodes().to$().find('.rowCheckbox').length ===
                                   table.rows({ search: 'applied' }).nodes().to$().find('.rowCheckbox:checked').length;
                $('#selectAll').prop('checked', allChecked);
            });

            // Select All checkbox (across current page only)
            $('#selectAll').on('click', function () {
                const isChecked = $(this).is(':checked');
                table.rows({ search: 'applied' }).nodes().to$().find('.rowCheckbox').each(function () {
                    const id = $(this).val();
                    $(this).prop('checked', isChecked);
                    if (isChecked) {
                        selectedStudentIds.add(id);
                    } else {
                        selectedStudentIds.delete(id);
                    }
                });
            });

            // Individual checkbox tracking
            $('#studentTable tbody').on('change', '.rowCheckbox', function () {
                const id = $(this).val();
                if ($(this).is(':checked')) {
                    selectedStudentIds.add(id);
                } else {
                    selectedStudentIds.delete(id);
                    $('#selectAll').prop('checked', false);
                }

                const allChecked = table.rows({ search: 'applied' }).nodes().to$().find('.rowCheckbox').length ===
                                   table.rows({ search: 'applied' }).nodes().to$().find('.rowCheckbox:checked').length;
                $('#selectAll').prop('checked', allChecked);
            });

            // Show Enroll Modal
            $('#enrollBtn').click(function () {
                if (selectedStudentIds.size === 0) {
                    alert("Please select at least one student to enroll.");
                    return;
                }
                $('#enrollModal').removeClass('hidden');
            });

            // Cancel Enroll Modal
            $('#cancelEnroll').click(function () {
                $('#enrollModal').addClass('hidden');
                $('#enrollForm')[0].reset();
            });

            // Submit Enrollment
            $('#enrollForm').submit(function (e) {
                e.preventDefault();

                const subjectId = $('#subjectSelect').val();
                if (!subjectId) {
                    alert('Please select a subject.');
                    return;
                }

                $.ajax({
                    url: 'enroll_subject.php',
                    method: 'POST',
                    data: {
                        student_ids: Array.from(selectedStudentIds),
                        subject_id: subjectId
                    },
                    success: function (response) {
                        try {
                            var res = JSON.parse(response);
                            if (res.success) {
                                alert('Students enrolled successfully!');
                                $('#enrollModal').addClass('hidden');
                                $('#enrollForm')[0].reset();
                                selectedStudentIds.clear();
                                $('#selectAll').prop('checked', false);
                                location.reload();
                            } else {
                                alert('Error: ' + res.message);
                            }
                        } catch (e) {
                            alert('Unexpected server response.');
                        }
                    },
                    error: function () {
                        alert('An error occurred while enrolling students.');
                    }
                });
            });

            // ✅ View Subjects Button (Delegated)
            $('#studentTable tbody').on('click', '.view-subjects-btn', function () {
                const studentId = $(this).data('student-id');
                $('#subjectsList').empty().append('<li>Loading...</li>');
                $('#viewSubjectsModal').removeClass('hidden').addClass('flex');

                $.ajax({
                    url: 'fetch_student_subjects.php',
                    method: 'GET',
                    data: { student_id: studentId },
                    success: function (response) {
                        $('#subjectsList').empty();
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                if (data.subjects.length === 0) {
                                    $('#subjectsList').append('<li>No subjects enrolled.</li>');
                                } else {
                                    data.subjects.forEach(subj => {
                                        $('#subjectsList').append(`<li>${subj.subject_name}</li>`);
                                    });
                                }
                            } else {
                                $('#subjectsList').append('<li>Error fetching subjects.</li>');
                            }
                        } catch (e) {
                            $('#subjectsList').append('<li>Invalid server response.</li>');
                        }
                    },
                    error: function () {
                        $('#subjectsList').empty().append('<li>Failed to fetch subjects.</li>');
                    }
                });
            });

            // Close View Subjects Modal
            $('#closeViewSubjects').on('click', function () {
                $('#viewSubjectsModal').removeClass('flex').addClass('hidden');
            });
        });
    </script>
    <!-- Background image div inserted here -->
    <div class="absolute inset-0 -z-20 bg-cover bg-center" style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')"></div>
</body>

</html>
