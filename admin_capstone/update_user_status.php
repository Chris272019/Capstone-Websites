<?php
// Include database connection
include('connection.php');

// Check if the required parameters are set
if (isset($_POST['userId']) && isset($_POST['action'])) {
    $userId = $_POST['userId'];
    $action = $_POST['action'];

    // Initialize the verification status and rejection reason
    $verificationStatus = '';
    $rejectionReason = '';

    // Set the verification status based on action
    if ($action === 'verify') {
        $verificationStatus = 'Verified';
        $rejectionReason = null; // No rejection reason if verified
    } elseif ($action === 'reject' && isset($_POST['rejectionReason'])) {
        $verificationStatus = 'Rejected';
        $rejectionReason = $_POST['rejectionReason'];
    }

    // Prepare the SQL query to update the user's verification status
    if ($verificationStatus === 'Rejected' && $rejectionReason) {
        // Update users table
        $sql_update_user = "UPDATE users SET verification_status = ? WHERE id = ?";

        // Insert rejection reason into account_rejection_reasons table
        $sql_insert_rejection_reason = "INSERT INTO account_rejection_reasons (user_id, reason) VALUES (?, ?)";

        // Begin transaction to ensure both actions are atomic
        $conn->begin_transaction();

        try {
            // Update the user's verification status
            if ($stmt_update_user = $conn->prepare($sql_update_user)) {
                $stmt_update_user->bind_param('si', $verificationStatus, $userId);
                $stmt_update_user->execute();
                $stmt_update_user->close();
            } else {
                throw new Exception("Failed to update user status");
            }

            // Insert the rejection reason
            if ($stmt_insert_rejection = $conn->prepare($sql_insert_rejection_reason)) {
                $stmt_insert_rejection->bind_param('is', $userId, $rejectionReason);
                $stmt_insert_rejection->execute();
                $stmt_insert_rejection->close();
            } else {
                throw new Exception("Failed to insert rejection reason");
            }

            // Commit the transaction if both operations are successful
            $conn->commit();

            echo 'User status updated and rejection reason recorded successfully';

        } catch (Exception $e) {
            // Rollback transaction if there is an error
            $conn->rollback();
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        // Update the verification status for verified users
        if ($verificationStatus === 'Verified') {
            $sql = "UPDATE users SET verification_status = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param('si', $verificationStatus, $userId);
                $stmt->execute();
                $stmt->close();
                echo 'User verified successfully';
            } else {
                echo 'Failed to update user status';
            }
        }
    }
} else {
    echo 'Invalid parameters';
}

$conn->close();
?>
