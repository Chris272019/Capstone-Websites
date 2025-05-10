<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    // Update the user's verification status to "Rejected"
    $sql = "UPDATE users SET verification_status = 'Rejected' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);

    if ($stmt->execute()) {
        // Send rejection email
        $sql = "SELECT email_address FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        
        if ($email) {
            mail($email, "Blood Donation Rejection", "You are not qualified for the blood donation.");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject user']);
    }
}
?>