<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedUsers = $_POST['selected_users'] ?? [];
    $donationDate = $_POST['donation_date'] ?? '';
    $donationTime = $_POST['donation_time'] ?? '';
    $location = $_POST['location'] ?? '';
    $status = $_POST['status'] ?? '';
    $scheduleType = $_POST['schedule_type'] ?? '';

    // Check if all fields are filled
    if (!empty($selectedUsers) && $donationDate && $donationTime && $location && $status && $scheduleType) {
        
        // Prepare the SQL query to avoid SQL injection
        $query = "UPDATE schedule SET donation_date = ?, donation_time = ?, location = ?, status = ?, schedule_type = ? 
                  WHERE user_id = ?";
        
        // Initialize prepared statement
        if ($stmt = mysqli_prepare($conn, $query)) {

            // Bind parameters to the prepared statement
            mysqli_stmt_bind_param($stmt, 'sssssi', $donationDate, $donationTime, $location, $status, $scheduleType, $userId);

            // Loop through each selected user and update their schedule
            foreach ($selectedUsers as $userId) {
                if (mysqli_stmt_execute($stmt)) {
                    echo "Schedule for user ID $userId successfully updated!<br>";
                    
                    // Insert the user_id into the initial_screening table after successful update
                    $insertScreeningQuery = "INSERT INTO initial_screening (user_id) VALUES (?)";
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
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($conn);
        }

    } else {
        echo "All fields are required! Please make sure to select at least one user and fill in all fields.";
    }
}
?>
