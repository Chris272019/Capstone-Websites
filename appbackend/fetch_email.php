<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$conn = new mysqli("localhost", "wh1422892_bloodbank", "bloodbank123", "wh1422892_bloodbank");

if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $conn->connect_error
    ]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = isset($data['email']) ? $data['email'] : '';

if (empty($email)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email is required'
    ]);
    exit;
}

// Prepare and execute query to check email
$stmt = $conn->prepare("SELECT email_address FROM users WHERE email_address = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email exists
    echo json_encode([
        'status' => 'success',
        'message' => 'Email found'
    ]);
} else {
    // Email not found
    echo json_encode([
        'status' => 'error',
        'message' => 'No email found. Please enter your registered email address.'
    ]);
}

$stmt->close();
$conn->close();
?> 