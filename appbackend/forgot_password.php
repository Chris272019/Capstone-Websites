<?php
header('Content-Type: application/json');
include 'connection.php';

// Get email from POST request
$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email is required'
    ]);
    exit;
}

try {
    // Check if email exists in users table
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email not found'
        ]);
        exit;
    }

    // Generate a random 6-digit verification code
    $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Update the verification code in the database
    $update_stmt = $conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
    $update_stmt->execute([$verification_code, $email]);

    // TODO: Send email with verification code
    // For now, we'll just return success
    echo json_encode([
        'status' => 'success',
        'message' => 'Verification code sent to your email'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 