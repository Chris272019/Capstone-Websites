<?php
// Include database connection
include('connection.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $blood_type = mysqli_real_escape_string($conn, $_POST['blood_type']);
    $collection_date = mysqli_real_escape_string($conn, $_POST['collection_date']);
    $expiration_date = mysqli_real_escape_string($conn, $_POST['expiration_date']);
    $volume_ml = mysqli_real_escape_string($conn, $_POST['volume_ml']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $collected_by = mysqli_real_escape_string($conn, $_POST['collected_by']);
    $plasma_type = isset($_POST['plasma_type']) ? mysqli_real_escape_string($conn, $_POST['plasma_type']) : null;
    
    // Current timestamp
    $created_at = date('Y-m-d H:i:s');
    
    // Insert into database
    $sql = "INSERT INTO blood_collection_inventory (
                user_id, 
                blood_type, 
                collection_date, 
                expiration_date, 
                volume_ml, 
                status, 
                collected_by, 
                created_at, 
                updated_at, 
                plasma_type
            ) VALUES (
                '$user_id', 
                '$blood_type', 
                '$collection_date', 
                '$expiration_date', 
                '$volume_ml', 
                '$status', 
                '$collected_by', 
                '$created_at', 
                '$created_at', 
                " . ($plasma_type ? "'$plasma_type'" : "NULL") . "
            )";
    
    if (mysqli_query($conn, $sql)) {
        // Success
        echo "Inventory item added successfully";
    } else {
        // Error
        echo "Error: " . mysqli_error($conn);
    }
    
    // Close the database connection
    mysqli_close($conn);
} else {
    // Not a POST request
    echo "Invalid request method";
}
?>