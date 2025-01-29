<?php
include('connection.php');

if (isset($_POST['user_id']) && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    $status = ($action === 'verify') ? 'Verified' : 'Rejected';

    // Update the verification status
    $sql = "UPDATE screening_answers SET verification_status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $user_id);

    if ($stmt->execute()) {
        // Handle rejection reason if action is 'reject'
        if ($action === 'reject') {
            // Use provided reason or default reason
            $reason = isset($_POST['reason']) && !empty(trim($_POST['reason']))
                ? $_POST['reason']
                : "User did not meet eligibility criteria for blood donation.";

            // Insert the rejection reason into the `reasons` table
            $reason_sql = "INSERT INTO reasons (user_id, reason_text) VALUES (?, ?)";
            $reason_stmt = $conn->prepare($reason_sql);
            $reason_stmt->bind_param('is', $user_id, $reason);

            if ($reason_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Verification status and rejection reason updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save rejection reason.']);
            }

            $reason_stmt->close();
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Verification status updated successfully.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update verification status.']);
    }
    $stmt->close();
}
?>
