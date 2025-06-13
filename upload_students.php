<?php
// Include the database configuration file to use the $conn connection
include('config.php');

// Check if a file is uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];

    // Check the file type (CSV)
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

    if ($fileType == 'csv') {
        // Handle CSV file
        if (($handle = fopen($fileTmpName, "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row > 0) {  // Skip header row
                    // Insert each row into the database
                    $first_name = $data[0];
                    $last_name = $data[1];
                    $rfid_number = $data[2];
                    $section = $data[3];
                    $school_year = $data[4];
                    $subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0; // Get subject_id from URL

                    // Assuming $conn is the database connection (from config.php)
                    $query = "INSERT INTO user (first_name, last_name, rfid_number, section, school_year, status, subject_id)
                              VALUES (?, ?, ?, ?, ?, 0, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssssi", $first_name, $last_name, $rfid_number, $section, $school_year, $subject_id);
                    $stmt->execute();
                }
                $row++;
            }
            fclose($handle);

            // Redirect to student.php with the subject_id
            header('Location: student.php?subject_id=' . $subject_id);
            exit(); // Don't forget to call exit() to stop further execution
        }
    } else {
        echo "<p>Invalid file type. Please upload a CSV file.</p>";
    }
}

// Close the connection
$conn->close();
?>
