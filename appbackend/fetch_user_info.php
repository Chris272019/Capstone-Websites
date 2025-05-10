<?php
include('connection.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Get the username from POST data
$username = $_POST['username'];

// Query to fetch user info based on username
$sql = "SELECT firstname, surname FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

// Check if user exists
if ($stmt->num_rows > 0) {
    // Bind result variables
    $stmt->bind_result($firstName, $surname);
    $stmt->fetch();

    // Return the user info as a JSON response
    echo json_encode([
        'firstname' => $firstName,
        'surname' => $surname
    ]);
} else {
    echo json_encode(['error' => 'User not found']);
}

// Close connection
$stmt->close();
$conn->close();
?>
