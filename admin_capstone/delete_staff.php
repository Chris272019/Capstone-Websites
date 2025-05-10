<?php
include('connection.php');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing staff ID']);
    exit;
}

// Sanitize input
$id = mysqli_real_escape_string($conn, $data['id']);

// Delete staff
$sql = "DELETE FROM staff_account WHERE id = '$id'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?> 