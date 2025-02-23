<?php
// Include the connection.php file for database connection
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get id from the form submission
    $id = $_POST['id'];
    
    // Check if the request status is 'Accepted' before marking as received
    $check_status_sql = "SELECT status FROM blood_request WHERE id = ?";
    $stmt = $conn->prepare($check_status_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($status);
        $stmt->fetch();
        
        if ($status == 'Accepted') {
            // Update the status to 'Received'
            $update_sql = "UPDATE blood_request SET status = 'Received' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $id);
            
            if ($update_stmt->execute()) {
                echo "<script>alert('Request marked as received successfully.'); window.location.href='hospital_dashboard.php';</script>";
            } else {
                echo "<script>alert('Error updating record.'); window.location.href='hospital_dashboard.php';</script>";
            }
            
            $update_stmt->close();
        } else {
            echo "<script>alert('Only accepted requests can be marked as received.'); window.location.href='hospital_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid request ID.'); window.location.href='hospital_dashboard.php';</script>";
    }
    
    $stmt->close();
}

$conn->close();
?>
