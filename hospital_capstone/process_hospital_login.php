<?php
// Start the session to store session data
session_start();

// Include your database connection file
include('connection.php'); // Ensure this contains the correct DB connection details

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the input values from the login form
    $hospital_name = isset($_POST['hospital_name']) ? $_POST['hospital_name'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Sanitize inputs to prevent SQL injection
    $hospital_name = mysqli_real_escape_string($conn, $hospital_name);
    $password = mysqli_real_escape_string($conn, $password);

    // Query to check the credentials and get the hospital's ID
    $sql = "SELECT id, password FROM hospital_accounts WHERE hospital_name = ?";

    // Prepare and execute the SQL statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $hospital_name); // Bind the hospital name parameter
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch the hospital data
            $hospital = $result->fetch_assoc();

            // Verify password (Ensure that passwords are hashed in your database)
            if ($password == $hospital['password']) {
                // Password is correct, start the session and store hospital ID
                $_SESSION['hospital_id'] = $hospital['id']; // Store the hospital's ID in the session

                // Redirect to the hospital dashboard or any other page after login
                header("Location: hospital_dashboard.php");
                exit;
            } else {
                // Invalid password
                echo "Invalid password.";
            }
        } else {
            // Hospital not found
            echo "Hospital not found.";
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // If prepare fails, display the error
        echo "Error preparing the SQL query: " . $conn->error;
    }
}
?>
 