<?php
// Start the session
session_start();

// Include the database connection
include('connection.php');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the logged-in staff ID
    if (!isset($_SESSION['staff_id'])) {
        echo "<p>Error: Staff ID not found in session.</p>";
        exit();
    }
    $staff_id = $_SESSION['staff_id'];

    // Get the user ID from the form submission
    if (!isset($_GET['user_id'])) {
        echo "<p>Error: User ID not provided.</p>";
        exit();
    }
    $user_id = $_GET['user_id'];

    // Get the blood collection details from the form
    $blood_type = $_POST['blood_type'];
    $amount_blood_taken = $_POST['amount_blood_taken'];
    $collection_date = date("Y-m-d H:i:s"); // Current date and time
    $expiration_date = date("Y-m-d H:i:s", strtotime("+42 days")); // Blood expires in 42 days
    $status = "Available"; // Default status when collected

    // Insert data into blood_collection_inventory
    $insert_query = "INSERT INTO blood_collection_inventory (user_id, blood_type, collection_date, expiration_date, volume_ml, status, collected_by, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    if ($stmt = $conn->prepare($insert_query)) {
        // Bind the parameters
        $stmt->bind_param("isssiss", $user_id, $blood_type, $collection_date, $expiration_date, $amount_blood_taken, $status, $staff_id);

        // Execute the query
        if ($stmt->execute()) {
            echo "<p>Blood collection inventory updated successfully.</p>";
            header("Location: blood_collection_patient.php"); // Redirect after successful update
            exit();
        } else {
            echo "<p>Error inserting into blood_collection_inventory: " . $stmt->error . "</p>";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "<p>Error preparing the query: " . $conn->error . "</p>";
    }

    // Close the database connection
    $conn->close();
} else {
    echo "<p>Invalid request.</p>";
}
?>
