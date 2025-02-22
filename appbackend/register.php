
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
// Set the header to allow cross-origin requests (optional, for development only)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database connection
require_once 'connection.php';  // Make sure to create this file with your DB connection details

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the POST data
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input fields
    if (!empty($username) && !empty($email) && !empty($password)) {
        
        // Hash the password for security purposes
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL query to insert user into the database
        $query = "INSERT INTO users (username, email_address, password) VALUES (?, ?, ?)";
        
        // Prepare statement
        if ($stmt = $conn->prepare($query)) {
            // Bind parameters and execute the statement
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                // Registration successful
                echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
            } else {
                // Registration failed
                echo json_encode(['status' => 'fail', 'message' => 'Failed to register user']);
            }

            // Close the statement
            $stmt->close();
        } else {
            // Error with prepared statement
            echo json_encode(['status' => 'fail', 'message' => 'Failed to prepare SQL statement']);
        }
    } else {
        // Missing fields
        echo json_encode(['status' => 'fail', 'message' => 'Please fill in all fields']);
    }

    // Close database connection
    $conn->close();
} else {
    // If not a POST request
    echo json_encode(['status' => 'fail', 'message' => 'Invalid request method']);
}
?>
