<?php
// Start the session
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Include database connection
include('connection.php');

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$user_id = $data['user_id'];

// Create a default entry in initial_screening table with pending status
$sql = "INSERT INTO initial_screening (user_id, status) VALUES (?, 'Pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'user_id' => $user_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating initial screening record: ' . $conn->error]);
}
?> 