<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('connection.php');
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hospital_name = mysqli_real_escape_string($conn, $_POST['hospital_name']);
    $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email_address = mysqli_real_escape_string($conn, $_POST['email_address']);
    $hospital_address = mysqli_real_escape_string($conn, $_POST['hospital_address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $zip_code = mysqli_real_escape_string($conn, $_POST['zip_code']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO hospital_accounts (hospital_name, contact_person, contact_number, email_address, hospital_address, city, province, zip_code, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssss", $hospital_name, $contact_person, $contact_number, $email_address, $hospital_address, $city, $province, $zip_code, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        sendEmailNotification($email_address, $hospital_name, $password);
        
        // Set success response for SweetAlert
        $response = array(
            'status' => 'success',
            'message' => 'Hospital account created successfully!'
        );
        echo json_encode($response);
        exit();
    } else {
        // Set error response for SweetAlert
        $response = array(
            'status' => 'error',
            'message' => 'Error adding hospital: ' . mysqli_error($conn)
        );
        echo json_encode($response);
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

function sendEmailNotification($email, $hospital_name, $password) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lifestream816@gmail.com';
        $mail->Password = 'unfzbojygxyxipfz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('lifestream816@gmail.com', 'LifeStream Blood Donation');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Hospital Registration Successful';
        $mail->Body = "<p>Dear $hospital_name,</p>
            <p>Your hospital has been successfully registered.</p>
            <p><strong>Login Details:</strong></p>
            <p><strong>Email:</strong> $hospital_name</p>
            <p><strong>Password:</strong> $password</p>
            <p>Best Regards,<br>LifeStream Team</p>";

        $mail->send();
    } catch (Exception $e) {
        error_log('Email sending failed: ' . $mail->ErrorInfo);
    }
}
?>