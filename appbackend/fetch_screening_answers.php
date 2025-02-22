<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database configuration
include 'connection.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if ($user_id) {
    // Query to fetch the screening answers for the given user_id
    $sql = "SELECT * FROM screening_answers WHERE user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind the user_id parameter to the SQL query
        $stmt->bind_param("i", $user_id);
        
        // Execute the query
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();
        
        // Check if a record is found
        if ($result->num_rows > 0) {
            // Fetch the data as an associative array
            $data = $result->fetch_assoc();
            
            // Prepare the response in JSON format
            $response = array(
                'status' => 'success',
                'data' => $data
            );
        } else {
            // No data found
            $response = array(
                'status' => 'error',
                'message' => 'No screening data found for this user.'
            );
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Error preparing the statement
        $response = array(
            'status' => 'error',
            'message' => 'Failed to prepare the SQL query.'
        );
    }
} else {
    // No user_id provided in the request
    $response = array(
        'status' => 'error',
        'message' => 'User ID is required.'
    );
}

// Close the database connection
$conn->close();

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
