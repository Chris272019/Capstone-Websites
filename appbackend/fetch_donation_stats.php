<?php
require 'connection.php'; // Include the database connection file
// Get user_id from POST request
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

if (empty($user_id)) {
    echo json_encode(["error" => "User ID is required"]);
    exit;
}

// Prepare SQL statement to get total donation quantity
$total_sql = "SELECT SUM(donation_quantity) as total_quantity FROM blood_donation_history WHERE user_id = ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("s", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_quantity = $total_row['total_quantity'] ? $total_row['total_quantity'] : 0;

// Prepare SQL statement to get the most recent donation date
$date_sql = "SELECT donation_date FROM blood_donation_history WHERE user_id = ? ORDER BY donation_date DESC LIMIT 1";
$date_stmt = $conn->prepare($date_sql);
$date_stmt->bind_param("s", $user_id);
$date_stmt->execute();
$date_result = $date_stmt->get_result();
$date_row = $date_result->fetch_assoc();
$last_donation_date = $date_row['donation_date'] ?? 'Not Donated Yet';

// Prepare SQL statement to get the next scheduled donation date and time
$next_sql = "SELECT donation_date, donation_time FROM schedule WHERE user_id = ? AND donation_date >= CURDATE() ORDER BY donation_date ASC LIMIT 1";
$next_stmt = $conn->prepare($next_sql);
$next_stmt->bind_param("s", $user_id);
$next_stmt->execute();
$next_result = $next_stmt->get_result();
$next_row = $next_result->fetch_assoc();

// Format next donation date and time if available
if ($next_row) {
    $next_donation = $next_row['donation_date'] . ' at ' . $next_row['donation_time'];
} else {
    $next_donation = 'Not Scheduled';
}

// Prepare response
$response = [
    "total_quantity" => (int) $total_quantity, // Converts "123.00" to 123
    "last_donation_date" => $last_donation_date,
    "next_donation" => $next_donation
];
echo json_encode($response);

// Close connection
$conn->close();
?>
