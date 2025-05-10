<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the database connection
include('connection.php');
require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

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
        // Send email with generated ID
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'lifestream816@gmail.com'; // Your Gmail
            $mail->Password = 'unfzbojygxyxipfz'; // Your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Change from ENCRYPTION_SMTPS to ENCRYPTION_STARTTLS
            $mail->Port = 587; // Change from 465 to 587

            // Email settings
            $mail->setFrom('lifestream816@gmail.com', 'Admin');
            $mail->addAddress($email_address);
            $mail->isHTML(true);
            $mail->Subject = 'Your Staff Account Details';
            $mail->Body = "<p>Dear $firstname $surname,</p>
                          <p>Your staff account has been created successfully.</p>
                          <p>Your Staff ID: <strong>$random_id</strong></p>
                          <p>Regards,<br>Admin</p>";
            
            $mail->send();

            // Redirect to the admin dashboard after success
            header("Location: admin_dashboard.php");
            exit(); // Don't forget to call exit to stop further script execution
        } catch (Exception $e) {
            echo 'Email Error: ' . $mail->ErrorInfo;
        }
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }

    // Close database connection
    mysqli_close($conn);
}
?>
