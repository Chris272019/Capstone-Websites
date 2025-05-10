<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection
include 'connection.php';


// Get POST data
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$donation_date = isset($_POST['donation_date']) ? $_POST['donation_date'] : null;
$donation_time = isset($_POST['donation_time']) ? $_POST['donation_time'] : null;

// Log received data for debugging
error_log("Received data - User ID: $user_id, Date: $donation_date, Time: $donation_time");

// Validate input
if (!$user_id || !$donation_date) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters'
    ]);
    exit;
}

// Sanitize input
$user_id = $conn->real_escape_string($user_id);
$donation_date = $conn->real_escape_string($donation_date);
$donation_time = $conn->real_escape_string($donation_time);

// Update the schedule status to "rescheduled"
// Using donation_date and donation_time to identify the specific schedule
$sql = "UPDATE schedule SET status = 'rescheduled' WHERE user_id = '$user_id' AND donation_date = '$donation_date'";

// Add donation_time to the query if it's provided
if ($donation_time) {
    $sql .= " AND donation_time = '$donation_time'";
}

error_log("SQL Query: $sql");

if ($conn->query($sql) === TRUE) {
    // Check if any rows were affected
    if ($conn->affected_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Appointment rescheduled successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No matching schedule found. Please check your database table structure.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating record: ' . $conn->error
    ]);
}

$conn->close();
?>