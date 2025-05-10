<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('connection.php');
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . mysqli_connect_error()]));
}

if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['user_id']);

    if ($action === 'verify') {
        $new_status = 'Verified';
    } elseif ($action === 'reject') {
        $new_status = 'Rejected';
    } else {
        die(json_encode(['status' => 'error', 'message' => 'Invalid action']));
    }

    // Retrieve user's email
    $query = "SELECT email_address FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();

        if (!empty($email)) {
            // Update verification status
            $sql = "UPDATE users SET verification_status = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("si", $new_status, $user_id);
                if ($stmt->execute()) {
                    $stmt->close();
                    sendEmailNotification($email);
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Verification status updated successfully!',
                        'action' => $action
                    ]);
                    exit;
                } else {
                    die(json_encode(['status' => 'error', 'message' => 'Error updating verification status: ' . $stmt->error]));
                }
            } else {
                die(json_encode(['status' => 'error', 'message' => 'Query preparation failed: ' . $conn->error]));
            }
        } else {
            die(json_encode(['status' => 'error', 'message' => 'User email not found']));
        }
    }
} else {
    die(json_encode(['status' => 'error', 'message' => 'Missing parameters']));
}

mysqli_close($conn);

function sendEmailNotification($email) {
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
        $mail->Subject = 'Verification Status Update';
        $mail->Body = "
            <p>Dear User,</p>
            <p>You are already scheduled. Please check our application to check the full details.</p>
            <p>Thank you for your support!</p>
            <p>Best Regards,<br>LifeStream Team</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email sending failed: ' . $mail->ErrorInfo);
        return false;
    }
}
?>