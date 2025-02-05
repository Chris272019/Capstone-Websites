<?php


// Include your database connection
include('connection.php');
session_start(); // Start the session

// Get the username and password from the POST request
$username = $_POST['username'];
$password = $_POST['password'];

// Set the response header to indicate JSON response
header('Content-Type: application/json');

// Prepare the SQL query to check if the username exists
$query = "SELECT * FROM users WHERE username = ?"; // Adjust query as per your database structure
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username); // Bind the username parameter
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User found, now check the password
    $user = $result->fetch_assoc();
    
    // Verify the password using password_verify (hashing is assumed to be done during registration)
    if (password_verify($password, $user['password'])) {
        // Store username in session
        $_SESSION['username'] = $user['username'];
    
        // Check if personal info is filled (adjust based on your structure)
        if (empty($user['surname']) || empty($user['firstname'])) {
            // Personal information is not filled
            echo json_encode([
                'status' => 'success',
                'hasPersonalInfo' => false, // Send false if personal info is missing
                'message' => 'Personal information missing. Please complete your profile.'
            ]);
        } else {
            // Personal information is filled
            echo json_encode([
                'status' => 'success',
                'hasPersonalInfo' => true,
                'message' => 'Login successful.'
            ]);
        }
    } else {
        // Password incorrect
        echo json_encode([
            'status' => 'failed',
            'message' => 'Invalid username or password.'
        ]);
    }
} else {
    // User not found
    echo json_encode([
        'status' => 'failed',
        'message' => 'Invalid username or password.'
    ]);
}

$stmt->close();
$conn->close();
?>
