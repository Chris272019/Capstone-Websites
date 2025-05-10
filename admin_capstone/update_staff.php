<?php
include('connection.php');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['id']) || !isset($data['firstname']) || !isset($data['surname']) || !isset($data['email']) || !isset($data['role'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Sanitize input
$id = mysqli_real_escape_string($conn, $data['id']);
$firstname = mysqli_real_escape_string($conn, $data['firstname']);
$surname = mysqli_real_escape_string($conn, $data['surname']);
$middlename = mysqli_real_escape_string($conn, $data['middlename']);
$email = mysqli_real_escape_string($conn, $data['email']);
$role = mysqli_real_escape_string($conn, $data['role']);

// Update staff information
$sql = "UPDATE staff_account SET 
        firstname = '$firstname',
        surname = '$surname',
        middlename = '$middlename',
        email_address = '$email',
        role = '$role'
        WHERE id = '$id'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?> 