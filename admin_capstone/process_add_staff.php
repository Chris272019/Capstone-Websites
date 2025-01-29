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
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Hash password before storing (for security)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert new staff account
    $sql = "INSERT INTO staff_account (firstname, surname, middlename, email_address, password, role) 
            VALUES ('$firstname', '$surname', '$middlename', '$email_address', '$hashed_password', '$role')";
    
    if (mysqli_query($conn, $sql)) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }

    // Close database connection
    mysqli_close($conn);
}
?>
