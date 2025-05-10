<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the connection.php file for database connection
include('connection.php');

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize form data
    $hospital_name = isset($_POST['hospital']) ? mysqli_real_escape_string($conn, $_POST['hospital']) : '';
    $blood_component = isset($_POST['blood_type']) ? mysqli_real_escape_string($conn, $_POST['blood_type']) : '';
    $blood_group = isset($_POST['blood_group']) ? mysqli_real_escape_string($conn, $_POST['blood_group']) : '';
    $amount_ml = isset($_POST['ml_amount']) ? (int)$_POST['ml_amount'] : 0;
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    
    // Map the blood type from the form to the allowed enum values if needed
    $allowed_components = ['Whole Blood', 'Packed RBC', 'Plasma', 'Platelets'];
    
    // Map blood components if necessary (adjust based on your form values)
    if ($blood_component == 'Fresh Frozen Plasma' || $blood_component == 'Leukocyte Poor Fresh Frozen Plasma') {
        $blood_component = 'Plasma';
    } else if ($blood_component == 'Platelet Concentrate' || $blood_component == 'Apheresis Platelets' || 
               $blood_component == 'Leukocyte Poor Platelet Concentrate') {
        $blood_component = 'Platelets';
    } else if ($blood_component == 'Washed RBC' || $blood_component == 'Buffy Coat Poor RBC') {
        $blood_component = 'Packed RBC';
    }
    
    // Validate if the blood_component is one of the allowed values
    if (!in_array($blood_component, $allowed_components)) {
        header("Location: admin_dashboard.php?error=Invalid blood component specified");
        exit();
    }
    
    // Validate if the blood_group is one of the allowed values
    $allowed_blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    if (!in_array($blood_group, $allowed_blood_groups)) {
        header("Location: admin_dashboard.php?error=Invalid blood group specified");
        exit();
    }
    
    // Validate amount
    if ($amount_ml <= 0) {
        header("Location: admin_dashboard.php?error=Amount must be greater than zero");
        exit();
    }
    
    // Validate hospital name
    if (empty($hospital_name)) {
        header("Location: admin_dashboard.php?error=Hospital name is required");
        exit();
    }
    
    // Insert data into admin_blood_request table
    $sql = "INSERT INTO admin_blood_request (hospital_name, blood_component, blood_group, amount_ml, patient_id) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssii", $hospital_name, $blood_component, $blood_group, $amount_ml, $request_id);
        
        if ($stmt->execute()) {
if (isset($_POST['skip_status_update']) && $_POST['skip_status_update'] === 'true') {
    // Skip status update
} else {
    // Update the blood_request status if the original request ID was provided
    if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
        $request_id = (int)$_POST['request_id'];
        $new_status = 'Pending';
        if (!empty(trim($new_status))) {
            $update_sql = "UPDATE blood_request SET status = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            
            if ($update_stmt) {
                $update_stmt->bind_param("si", $new_status, $request_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
    }
}
            
            // Redirect with success message
            header("Location: admin_dashboard.php?success=1");
            exit();
        } else {
            // Handle database error
            header("Location: admin_dashboard.php?error=Database error: " . $stmt->error);
            exit();
        }
        
        $stmt->close();
    } else {
        // Handle statement preparation error
        header("Location: admin_dashboard.php?error=Database error: " . $conn->error);
        exit();
    }
} else {
    // If not POST request, redirect to dashboard
    header("Location: admin_dashboard.php");
    exit();
}

// Close database connection
$conn->close();
?> 