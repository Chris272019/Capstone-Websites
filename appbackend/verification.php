<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include 'connection.php'; 
// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';

if (empty($username)) {
    die(json_encode(['status' => 'error', 'message' => 'Username is required']));
}

// Generate a random 6-digit verification code
$verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Update the verification code in the users table
$stmt = $conn->prepare("UPDATE users SET verification_code = ? WHERE username = ?");
$stmt->bind_param("ss", $verification_code, $username);

if ($stmt->execute()) {
    // Here you would typically send the verification code via email
    // For now, we'll just return it in the response
    echo json_encode([
        'status' => 'success',
        'message' => 'Verification code generated successfully',
        'verification_code' => $verification_code // Remove this in production
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to generate verification code'
    ]);
}

$stmt->close();
$conn->close();
?> 