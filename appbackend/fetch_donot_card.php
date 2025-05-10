<?php
header('Content-Type: application/json');
include 'connection.php';

if (!isset($_GET['user_id'])) {
    echo json_encode(["error" => "User ID is required"]);
    exit;
}

$user_id = intval($_GET['user_id']);

$user_query = "SELECT firstname, surname, blood_group FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_query);

// Check if the statement was prepared successfully
if (!$stmt_user) {
    echo json_encode(["error" => "User query failed: " . $conn->error]);
    exit;
}

$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows == 0) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

$user = $user_result->fetch_assoc();

// Fetch donation history
$donation_query = "SELECT donation_date, donation_quantity FROM blood_donation_history WHERE user_id = ? ORDER BY donation_date DESC";
$stmt_donation = $conn->prepare($donation_query);

if (!$stmt_donation) {
    echo json_encode(["error" => "Donation query failed: " . $conn->error]);
    exit;
}

$stmt_donation->bind_param("i", $user_id);
$stmt_donation->execute();
$donation_result = $stmt_donation->get_result();

$donation_history = [];
$total_donation = 0;

while ($row = $donation_result->fetch_assoc()) {
    $total_donation += (float) $row['donation_quantity']; // Ensure numeric format
    $donation_history[] = $row;
}

// Ensure no unexpected output
$response = [
    "firstname" => $user['firstname'],
    "surname" => $user['surname'],
    "blood_group" => $user['blood_group'],
    "total_donation" => $total_donation,
    "donation_history" => $donation_history,
];

ob_clean();
echo json_encode($response, JSON_PRETTY_PRINT);
exit;
?>
