<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedUsers = $_POST['selected_users'] ?? [];
    $donationDate = $_POST['donation_date'] ?? '';
    $donationTime = $_POST['donation_time'] ?? '';
    $location = $_POST['location'] ?? '';

    // Automatically set status and schedule type
    $status = "Scheduled";
    $scheduleType = "Initial Screening";

    if (!empty($selectedUsers) && $donationDate && $donationTime && $location) {
        foreach ($selectedUsers as $userId) {
            // Check if user already has a schedule
            $checkQuery = "SELECT * FROM schedule WHERE user_id = ?";
            $stmtCheck = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($stmtCheck, 'i', $userId);
            mysqli_stmt_execute($stmtCheck);
            mysqli_stmt_store_result($stmtCheck);
            $rowCount = mysqli_stmt_num_rows($stmtCheck);
            mysqli_stmt_close($stmtCheck);

            if ($rowCount > 0) {
                // Update existing schedule
                $query = "UPDATE schedule SET donation_date = ?, donation_time = ?, location = ?, status = ?, schedule_type = ? WHERE user_id = ?";
            } else {
                // Insert new schedule if none exists
                $query = "INSERT INTO schedule (user_id, donation_date, donation_time, location, status, schedule_type) VALUES (?, ?, ?, ?, ?, ?)";
            }

            if ($stmt = mysqli_prepare($conn, $query)) {
                if ($rowCount > 0) {
                    mysqli_stmt_bind_param($stmt, 'sssssi', $donationDate, $donationTime, $location, $status, $scheduleType, $userId);
                } else {
                    mysqli_stmt_bind_param($stmt, 'isssss', $userId, $donationDate, $donationTime, $location, $status, $scheduleType);
                }

                if (mysqli_stmt_execute($stmt)) {
                    echo "Schedule for user ID $userId successfully updated/added.<br>";

                    // Insert into initial_screening table
                    $insertScreeningQuery = "INSERT INTO initial_screening (user_id) VALUES (?) ON DUPLICATE KEY UPDATE user_id = user_id";
                    if ($insertStmt = mysqli_prepare($conn, $insertScreeningQuery)) {
                        mysqli_stmt_bind_param($insertStmt, 'i', $userId);
                        if (mysqli_stmt_execute($insertStmt)) {
                            echo "User ID $userId successfully added to initial_screening.<br>";
                        } else {
                            echo "Error inserting user ID $userId into initial_screening: " . mysqli_error($conn) . "<br>";
                        }
                        mysqli_stmt_close($insertStmt);
                    } else {
                        echo "Error preparing insert statement for initial_screening: " . mysqli_error($conn) . "<br>";
                    }
                } else {
                    echo "Error updating schedule for user ID $userId: " . mysqli_error($conn) . "<br>";
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "Error preparing statement: " . mysqli_error($conn);
            }
        }
    } else {
        echo "All fields are required! Please make sure to select at least one user and fill in all fields.";
    }
}
?>
