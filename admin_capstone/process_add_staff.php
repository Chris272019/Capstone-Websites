<?php
// Include the database connection
include('connection.php');

// Check if form data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $surname = mysqli_real_escape_string($conn, $_POST['surname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $email_address = mysqli_real_escape_string($conn, $_POST['email_address']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Generate a random 5-digit ID
    $random_id = rand(10000, 99999);

    // SQL query to insert new staff account without the password column
    $sql = "INSERT INTO staff_account (id, firstname, surname, middlename, email_address, role) 
            VALUES ('$random_id', '$firstname', '$surname', '$middlename', '$email_address', '$role')";
    
    if (mysqli_query($conn, $sql)) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }

    // Close database connection
    mysqli_close($conn);
}
?>
