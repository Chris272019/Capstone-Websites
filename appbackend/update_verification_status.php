<?php
// Include database connection
include('connection.php');  // Ensure you have a proper DB connection file

// Check if the required parameters are provided
if (isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = $_POST['user_id'];  // Get user ID from the POST data
    $status = $_POST['status'];  // Get status from the POST data (Verified/Rejected)

    // Validate status to ensure it's either 'Verified' or 'Rejected'
    if ($status !== 'Verified' && $status !== 'Rejected') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit();
    }

    // Prepare SQL query to update the verification_status in the screening_answers table
    $sql = "UPDATE screening_answers SET verification_status = ? WHERE user_id = ?";

    // Check if the statement is prepared successfully
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the prepared statement
        $stmt->bind_param("si", $status, $user_id);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User verification status updated']);
        } else {
            // Log query error
            error_log("Error executing query: " . $stmt->error);  // Log to error log
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
        }
        $stmt->close();
    } else {
        // Log SQL preparation error
        error_log("Error preparing query: " . $conn->error);  // Log to error log
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }

    // Close the database connection
    $conn->close();
} else {
    // Return error if required parameters are missing
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}
?>