<?php
include('connection.php');

if (isset($_POST['user_id']) && isset($_POST['action']) && $_POST['action'] === 'reject') {
    $user_id = intval($_POST['user_id']);

    // Use provided reason or default reason
    $reason = isset($_POST['reason']) && !empty(trim($_POST['reason']))
        ? $_POST['reason']
        : "User did not meet eligibility criteria for blood donation.";

    // Insert the rejection reason into the `reasons` table
    $reason_sql = "INSERT INTO reasons (user_id, reason_text) VALUES (?, ?)";
    $reason_stmt = $conn->prepare($reason_sql);
    $reason_stmt->bind_param('is', $user_id, $reason);

    if ($reason_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Rejection reason saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save rejection reason.']);
    }

    $reason_stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or missing parameters.']);
}
?>
