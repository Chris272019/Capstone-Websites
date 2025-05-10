<?php
// Include database connection
include('connection.php');
require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);

    // Retrieve user's email
    $query = "SELECT email_address FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $email);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($email) {
        // Update user verification status
        $sql = "UPDATE users SET verification_status = 'verified' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);

        if (mysqli_stmt_execute($stmt)) {
            // Send verification email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'lifestream816@gmail.com'; // Your Gmail
                $mail->Password = 'unfzbojygxyxipfz'; // Your App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email settings
                $mail->setFrom('lifestream816@gmail.com', 'Admin');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Account Verification Successful';
                $mail->Body = "
                    <p>Dear User,</p>
                    <p>Your account has been successfully verified.</p>
                    <p>You can now access all features of our platform.</p>
                    <p>Regards,<br>Admin Team</p>
                ";

                $mail->send();
                echo json_encode(['success' => true, 'message' => 'Verification successful and email sent.']);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Verification successful, but email failed: ' . $mail->ErrorInfo
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . mysqli_error($conn)
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

    mysqli_close($conn);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>
