<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

include('config.php');
$user_id = $_SESSION['user_logged_in'];

// Fetch students
$student_query = "SELECT * FROM student WHERE status=0";
$student_result = mysqli_query($conn, $student_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Student</title>
    <link rel="icon" type="image/x-icon" href="./img/l.png">
    <script src="./css/3.4.16"></script>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/jquery.dataTables.min.js"></script>
    <link href="./css/jquery.dataTables.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">

    <!-- Background image div inserted here -->
    <div class="absolute inset-0 -z-20 bg-cover bg-center" style="background-image: url('./img/Mariano-Marcos-State-University-MMSU.jpg')"></div>


    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-10 min-h-screen">
            <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-6xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Manage Student</h1>
<?php if (isset($_SESSION['import_success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= $_SESSION['import_success'] ?>
    </div>
    <?php unset($_SESSION['import_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['import_error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 overflow-auto max-h-48">
        <?= $_SESSION['import_error'] ?>
    </div>
    <?php unset($_SESSION['import_error']); ?>
<?php endif; ?>

                <!-- Add Student Button -->
                <button id="openModalBtn"
                    class="text-white px-4 py-2 rounded-md mb-6 hover:bg-red-900 transition" style="background-color: #4B0000;">
                    Add Student
                </button>

                <form action="import_students.php" method="POST" enctype="multipart/form-data" class="mb-6">
    <label class="block font-medium mb-2">Import Students (CSV):</label>
    <input type="file" name="import_file" accept=".csv" required class="border px-3 py-2 rounded mb-2">
    <button type="submit" class="text-white px-4 py-2 rounded-md mb-6 hover:bg-red-900 transition" style="background-color: #4B0000;">
        Import CSV
    </button>
    <a href="./sample.csv" class="ml-4 text-blue-600 underline">Download Sample CSV</a>
</form>


                <!-- Students Table -->
                <table id="studentTable" class="display stripe hover" style="width:100%">
                    <thead>
                        <tr>
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
                        <?php while ($row = mysqli_fetch_assoc($student_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['first_name']) ?></td>
                                <td><?= htmlspecialchars($row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['rfid_number']) ?></td>
                                <td><?= htmlspecialchars($row['section']) ?></td>
                                <td><?= htmlspecialchars($row['school_year']) ?></td>
                                <td>
                                    <button class="editBtn text-blue-600 hover:underline mr-2" data-id="<?= $row['id'] ?>"
                                        data-first_name="<?= htmlspecialchars($row['first_name'], ENT_QUOTES) ?>"
                                        data-last_name="<?= htmlspecialchars($row['last_name'], ENT_QUOTES) ?>"
                                        data-rfid_number="<?= htmlspecialchars($row['rfid_number'], ENT_QUOTES) ?>"
                                        data-section="<?= htmlspecialchars($row['section'], ENT_QUOTES) ?>"
                                        data-school_year="<?= htmlspecialchars($row['school_year'], ENT_QUOTES) ?>"
                                        data-status="<?= $row['status'] ?>">
                                        Edit
                                    </button>
                                    <button class="removeBtn text-red-600 hover:underline" data-id="<?= $row['id'] ?>">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Modal Background -->
                <div id="modalBg" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
                    <!-- Modal -->
                    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                        <h2 class="text-xl font-semibold mb-4">Add Student</h2>
                        <form id="addStudentForm">
                            <div class="mb-3">
                                <label for="first_name" class="block mb-1 font-medium">First Name</label>
                                <input type="text" id="first_name" name="first_name"
                                    class="w-full border rounded px-3 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="block mb-1 font-medium">Last Name</label>
                                <input type="text" id="last_name" name="last_name"
                                    class="w-full border rounded px-3 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label for="rfid_number" class="block mb-1 font-medium">RFID Number</label>
                                <input type="text" id="rfid_number" name="rfid_number"
                                    class="w-full border rounded px-3 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label for="section" class="block mb-1 font-medium">Section</label>
                                <select id="section" name="section" class="w-full border rounded px-3 py-2" required>
                                    <option value="" disabled selected>Select Section</option>
                                    <option value="1-A">1-A</option>
                                    <option value="1-B">1-B</option>
                                    <option value="2-A">2-A</option>
                                    <option value="2-B">2-B</option>
                                    <option value="3-A">3-A</option>
                                    <option value="3-B">3-B</option>
                                    <option value="4-A">4-A</option>
                                    <option value="4-B">4-B</option>
                                    <option value="5-A">5-A</option>
                                    <option value="5-B">5-B</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="school_year" class="block mb-1 font-medium">School Year</label>
                                <select id="school_year" name="school_year" class="w-full border rounded px-3 py-2"
                                    required>
                                    <!-- options will be dynamically inserted -->
                                </select>
                            </div>

                            <div class="mb-3 hidden">
                                <label for="status" class="block mb-1 font-medium">Status</label>
                                <select id="status" name="status" class="w-full border rounded px-3 py-2" required>
                                    <option value="0" selected>Active</option>
                                    <option value="1">Deleted</option>
                                </select>
                            </div>


                            <div class="flex justify-end gap-2">
                                <button type="button" id="closeModalBtn"
                                    class="px-4 py-2 rounded bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                                <button type="submit"
                                    class="px-4 py-2 rounded hover:bg-red-900 text-white transition" style="background-color: #4B0000;">Save</button>
                            </div>
                        </form>
                    </div>
                </div>


                <div id="editModalBg" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
                    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                        <h2 class="text-xl font-semibold mb-4">Edit Student</h2>
                        <form id="editStudentForm">
                            <input type="hidden" id="edit_id" name="id">
                            <div class="mb-3">
                                <label for="edit_first_name" class="block mb-1 font-medium">First Name</label>
                                <input type="text" id="edit_first_name" name="first_name"
                                    class="w-full border rounded px-3 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_last_name" class="block mb-1 font-medium">Last Name</label>
                                <input type="text" id="edit_last_name" name="last_name"
                                    class="w-full border rounded px-3 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_rfid_number" class="block mb-1 font-medium">RFID Number</label>
                                <input type="text" id="edit_rfid_number" name="rfid_number"
                                    class="w-full border rounded px-3 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_section" class="block mb-1 font-medium">Section</label>
                                <select id="edit_section" name="section" class="w-full border rounded px-3 py-2"
                                    required>
                                    <option value="" disabled>Select Section</option>
                                    <option value="1-A">1-A</option>
                                    <option value="1-B">1-B</option>
                                    <option value="2-A">2-A</option>
                                    <option value="2-B">2-B</option>
                                    <option value="3-A">3-A</option>
                                    <option value="3-B">3-B</option>
                                    <option value="4-A">4-A</option>
                                    <option value="4-B">4-B</option>
                                    <option value="5-A">5-A</option>
                                    <option value="5-B">5-B</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_school_year" class="block mb-1 font-medium">School Year</label>
                                <select id="edit_school_year" name="school_year" class="w-full border rounded px-3 py-2"
                                    required>
                                    <!-- Options will be filled by JS -->
                                </select>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" id="closeEditModalBtn"
                                    class="px-4 py-2 rounded bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                                <button type="submit"
                                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Save
                                    Changes</button>
                            </div>
                        </form>
                    </div>
                </div>


                <script>
                    $(document).ready(function () {
                        // Initialize DataTable
                        $('#studentTable').DataTable();

                        // Show modal
                        $('#openModalBtn').on('click', function () {
                            $('#modalBg').removeClass('hidden').addClass('flex');
                        });

                        // Hide modal
                        $('#closeModalBtn').on('click', function () {
                            $('#modalBg').removeClass('flex').addClass('hidden');
                            $('#addStudentForm')[0].reset();
                        });

                        // Submit form
                        $('#addStudentForm').on('submit', function (e) {
                            e.preventDefault();

                            $.ajax({
                                url: 'add_students.php',
                                type: 'POST',
                                data: $(this).serialize(),
                                success: function (response) {
                                    let res = JSON.parse(response);
                                    if (res.success) {
                                        alert('Student added successfully!');
                                        location.reload(); // reload page to show new data, or you can dynamically add row
                                    } else {
                                        alert('Error: ' + res.message);
                                    }
                                },
                                error: function () {
                                    alert('An error occurred while saving the student.');
                                }
                            });
                        });
                    });

                    $(document).ready(function () {
                        const select = $('#school_year');
                        const now = new Date();
                        const year = now.getFullYear();

                        const option1 = `${year}-${year + 1}`;
                        const option2 = `${year + 1}-${year + 2}`;

                        select.append(`<option value="${option1}">${option1}</option>`);


                        // Optionally select the current school year by default
                        select.val(option1);
                    });

                    $(document).ready(function () {
                        // Populate school year options in edit modal
                        const editSchoolYearSelect = $('#edit_school_year');
                        const now = new Date();
                        const year = now.getFullYear();
                        const years = [`${year}-${year + 1}`, `${year + 1}-${year + 2}`];
                        years.forEach(y => editSchoolYearSelect.append(`<option value="${y}">${y}</option>`));

                        // Open edit modal and fill values
                        

                        // Close edit modal
                        $('#closeEditModalBtn').on('click', function () {
                            $('#editModalBg').removeClass('flex').addClass('hidden');
                            $('#editStudentForm')[0].reset();
                        });

                        // Submit edit form via AJAX
                        $('#editStudentForm').on('submit', function (e) {
                            e.preventDefault();

                            $.ajax({
                                url: 'edit_students.php',
                                method: 'POST',
                                data: $(this).serialize(),
                                success: function (response) {
                                    let res = JSON.parse(response);
                                    if (res.success) {
                                        alert('Student updated successfully!');
                                        location.reload();
                                    } else {
                                        alert('Error: ' + res.message);
                                    }
                                },
                                error: function () {
                                    alert('An error occurred while updating the student.');
                                }
                            });
                        });

                        // Remove student (set status=1)
                        $('#studentTable').on('click', '.editBtn', function () {
                            const btn = $(this);
                        
                            $('#edit_id').val(btn.data('id'));
                            $('#edit_first_name').val(btn.data('first_name'));
                            $('#edit_last_name').val(btn.data('last_name'));
                            $('#edit_rfid_number').val(btn.data('rfid_number'));
                            $('#edit_section').val(btn.data('section'));
                            $('#edit_school_year').val(btn.data('school_year'));
                            $('#editModalBg').removeClass('hidden').addClass('flex');
                        });

                        $('#studentTable').on('click', '.removeBtn', function () {
                            if (!confirm('Are you sure you want to remove this student?')) return;

                            let studentId = $(this).data('id');

                            $.ajax({
                                url: 'remove_students.php',
                                method: 'POST',
                                data: { id: studentId },
                                success: function (response) {
                                    let res = JSON.parse(response);
                                    if (res.success) {
                                        alert('Student removed successfully!');
                                        location.reload();
                                    } else {
                                        alert('Error: ' + res.message);
                                    }
                                },
                                error: function () {
                                    alert('An error occurred while removing the student.');
                                }
                            });
                    });
                    });
                    $('.editBtn').on('click', function () {
                        // ...
                    });

                    $('.removeBtn').on('click', function () {
                        // ...
                    });
                </script>




</body>

</html>