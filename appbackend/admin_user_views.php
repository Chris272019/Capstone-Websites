<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection
include 'connection.php';

// Query to fetch users including the ID
$sql = "SELECT id, username, firstname, surname, email_address FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Initialize an array to hold user data
    $users = [];
    
    // Fetch each row and append to the $users array
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    // Return JSON response
    echo json_encode([
        'status' => 'success',
        'data' => $users
    ]);
} else {
    // If no users found, return error message in JSON format
    echo json_encode([
        'status' => 'error',
        'message' => 'No users found'
    ]);
}

// Close database connection
$conn->close();
?>
