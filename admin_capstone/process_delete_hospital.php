<?php
// Include database connection
include('connection.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check if the request is POST and has an ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $hospital_id = $_POST['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete all blood requests associated with this hospital
        $delete_requests_sql = "DELETE FROM blood_request WHERE hospital_id = ?";
        $stmt = $conn->prepare($delete_requests_sql);
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        
        // Then delete the hospital
        $delete_hospital_sql = "DELETE FROM hospital_accounts WHERE id = ?";
        $stmt = $conn->prepare($delete_hospital_sql);
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Hospital and associated blood requests deleted successfully']);
    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?> 