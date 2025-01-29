<?php
// Start the session
session_start();

// Database connection
include('connection.php');

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Query to check credentials
    $sql = "SELECT id FROM admin_login WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);

    // Bind the input parameters
    $stmt->bind_param("ss", $inputUsername, $inputPassword);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if the username and password match a row in the database
    if ($result->num_rows > 0) {
        // Fetch the admin details
        $admin = $result->fetch_assoc();

        // Store the admin ID in the session
        $_SESSION['admin_id'] = $admin['id'];

        // Redirect to the admin dashboard
        echo "<script>alert('Login successful!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        // Login failed
        echo "<script>alert('Invalid username or password!'); window.location.href='login.html';</script>";
    }
}
?>
