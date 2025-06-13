<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $users_id = $_POST['users_id'];
    $schedule_id = $_POST['schedule_id'];
    $subject_id = $_POST['subject_id'];

    // Check if the combination already exists
    $check_sql = "SELECT * FROM student_schedule WHERE users_id = ? AND schedule_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $users_id, $schedule_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Duplicate found
        echo "<script>
            alert('Error: This user is already assigned to this schedule!');
            window.location.href='managesubject.php?id=$subject_id';
        </script>";
    } else {
        // No duplicate, proceed with insert
        $sql = "INSERT INTO student_schedule (users_id, schedule_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $users_id, $schedule_id);

        if ($stmt->execute()) {
            echo "<script>
                alert('Schedule assigned successfully!');
                window.location.href='managesubject.php?id=$subject_id';
            </script>";
        } else {
            echo "<script>
                alert('Failed to assign schedule.');
                window.location.href='managesubject.php?id=$subject_id';
            </script>";
        }

        $stmt->close();
    }

    $check_stmt->close();
    $conn->close();
}
?>
