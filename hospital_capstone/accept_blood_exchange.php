<?php
session_start();
include('connection.php');

if (isset($_POST['exchange_id'], $_POST['patient_id'])) {
    $exchange_id = (int)$_POST['exchange_id'];
    $patient_id = (int)$_POST['patient_id'];

    // Debugging: log exchange_id and patient_id
    error_log("Exchange ID: " . $exchange_id);
    error_log("Patient ID: " . $patient_id);

    // Check if this is an acceptance or rejection
    $is_rejection = isset($_POST['action']) && $_POST['action'] === 'reject';
    
    // For rejection, we need a reason
    $rejection_reason = null;
    if ($is_rejection && isset($_POST['rejection_reason'])) {
        $rejection_reason = $_POST['rejection_reason'];
    }

    // Check if the request exists in the database
    $sql_check = "SELECT * FROM admin_blood_request WHERE request_id = ? AND patient_id = ?";
    $stmt_check = $conn->prepare($sql_check);

    if ($stmt_check) {
        $stmt_check->bind_param("ii", $exchange_id, $patient_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // Proceed with the update if the request exists
            $stmt_check->close();

            if ($is_rejection) {
                // Handle rejection
                $sql = "UPDATE admin_blood_request SET status = 'Blood not available', reject_reason = ? WHERE request_id = ? AND patient_id = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("sii", $rejection_reason, $exchange_id, $patient_id);
                    
                    if ($stmt->execute()) {
                        error_log("Rows affected: " . $stmt->affected_rows);
                        $_SESSION['message'] = "Blood exchange request has been rejected.";
                        error_log("Blood exchange request for Patient ID #$patient_id rejected successfully.");
                    } else {
                        $_SESSION['error'] = "Execution failed: " . $stmt->error;
                        error_log("Execution failed: " . $stmt->error);
                    }
                    
                    $stmt->close();
                } else {
                    $_SESSION['error'] = "SQL Prepare failed: " . $conn->error;
                    error_log("SQL Prepare failed: " . $conn->error);
                }
            } else {
                // Handle acceptance
                $sql = "UPDATE admin_blood_request SET status = 'Accepted' WHERE request_id = ? AND patient_id = ?";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("ii", $exchange_id, $patient_id);

                    if ($stmt->execute()) {
                        error_log("Rows affected: " . $stmt->affected_rows);
                        $_SESSION['message'] = "Blood exchange request has been accepted.";
                        error_log("Blood exchange request for Patient ID #$patient_id updated successfully.");
                    } else {
                        $_SESSION['error'] = "Execution failed: " . $stmt->error;
                        error_log("Execution failed: " . $stmt->error);
                    }

                    $stmt->close();
                } else {
                    $_SESSION['error'] = "SQL Prepare failed: " . $conn->error;
                    error_log("SQL Prepare failed: " . $conn->error);
                }
            }
        } else {
            // Log and display error if request doesn't exist
            $_SESSION['error'] = "No such exchange request found for Patient ID: $patient_id and Exchange ID: $exchange_id.";
            error_log("No such exchange request found for Patient ID: $patient_id and Exchange ID: $exchange_id");
        }
    } else {
        $_SESSION['error'] = "SQL Check failed: " . $conn->error;
        error_log("SQL Check failed: " . $conn->error);
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    error_log("Invalid request. Missing exchange_id or patient_id.");
}

// Redirect back to view_blood_exchange.php with patient_id
header("Location: view_blood_exchange.php?patient_id=" . urlencode($patient_id));
exit();
?>
