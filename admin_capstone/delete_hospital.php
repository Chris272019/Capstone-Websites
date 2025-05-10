<?php
// Include database connection
include('connection.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to return JSON
header('Content-Type: application/json');

// Debug: Log the received data
error_log("Received POST data: " . print_r($_POST, true));

// Check if the hospital_id is provided
if (isset($_POST['hospital_id']) && !empty($_POST['hospital_id'])) {
    try {
        // Sanitize the hospital ID to prevent SQL injection
        $hospital_id = $conn->real_escape_string($_POST['hospital_id']);
        error_log("Attempting to delete hospital with ID: " . $hospital_id);

        // First, check if the hospital exists
        $check_sql = "SELECT id FROM hospital_accounts WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        
        if (!$check_stmt) {
            throw new Exception('Prepare failed for check: ' . $conn->error);
        }
        
        $check_stmt->bind_param("i", $hospital_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            throw new Exception('Hospital account not found');
        }
        
        $check_stmt->close();

        // Start transaction
        $conn->begin_transaction();

        try {
            // SQL query to delete the hospital account based on the provided ID
            $sql = "DELETE FROM hospital_accounts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }

            $stmt->bind_param("i", $hospital_id);

            // Execute the query
            if ($stmt->execute()) {
                error_log("Successfully deleted hospital with ID: " . $hospital_id);
                $conn->commit();
                // Return success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Hospital account deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete hospital account: ' . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Error in delete_hospital.php: " . $e->getMessage());
        // Return error response if an exception occurs
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
} else {
    error_log("Hospital ID is missing in the request");
    // Return error if hospital_id is not provided
    echo json_encode([
        'success' => false, 
        'message' => 'Hospital ID is missing.'
    ]);
}

// Close the database connection
$conn->close();
?>
