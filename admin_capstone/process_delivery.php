<?php
include('connection.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the incoming data
    error_log("Received POST data: " . print_r($_POST, true));
    
    $inventory_id = $_POST['id'];
    $hospital_id = $_POST['hospital_id'];
    $number_of_bags = $_POST['number_of_bags']; // Get the calculated number of bags

    // Validate input
    if (!is_numeric($inventory_id) || !is_numeric($hospital_id) || !is_numeric($number_of_bags)) {
        http_response_code(400);
        echo "Invalid input parameters";
        exit;
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Get the current inventory item details
        $inventory_query = "SELECT * FROM blood_collection_inventory WHERE id = ? AND status = 'Available'";
        $stmt = mysqli_prepare($conn, $inventory_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $inventory_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $inventory_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($inventory_result) === 0) {
            throw new Exception("Blood unit not available or already reserved.");
        }

        $inventory_item = mysqli_fetch_assoc($inventory_result);
        $total_ml = $inventory_item['volume_ml'];
        $available_bags = $inventory_item['number_of_bags'];
        
        // Get the blood type and blood group from the inventory item
        $blood_type_combined = $inventory_item['blood_type']; // Already in combined format (e.g., "A+")
        $blood_group = $inventory_item['blood_group'];
        
        // Override blood type and group with the requested values
        $blood_type_combined = "A+"; // Set to A+ as requested
        $blood_group = "leukocyte_poor_fresh_frozen_plasma"; // Set as requested
        
        // Calculate total ml based on number of bags (assuming 450ml per bag)
        $ml_per_bag = 450;
        $requested_ml = $number_of_bags * $ml_per_bag;
        
        // Log the inventory details
        error_log("Inventory details - Total ML: $total_ml, Available Bags: $available_bags, Requested ML: $requested_ml");
        error_log("Blood Type: $blood_type_combined, Blood Group: $blood_group");
        
        // Check if we have enough bags
        if ($number_of_bags > $available_bags) {
            throw new Exception("Not enough blood bags available. Maximum available: " . $available_bags . " bags");
        }

        // Calculate remaining volume and bags
        $remaining_ml = $total_ml - $requested_ml;
        $remaining_bags = $available_bags - $number_of_bags;

        // Check if remaining volume is valid
        if ($remaining_ml < 0) {
            throw new Exception("Not enough blood volume available. Maximum available: " . $total_ml . "ml");
        }

        // If inventory is depleted, delete the record instead of updating
        if ($remaining_ml <= 0 || $remaining_bags <= 0) {
            $delete_inventory = "DELETE FROM blood_collection_inventory WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_inventory);
            if (!$stmt) {
                throw new Exception("Prepare failed for delete: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $inventory_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed for delete: " . mysqli_stmt_error($stmt));
            }
        } else {
            // Update the main inventory without changing status
            $update_inventory = "UPDATE blood_collection_inventory 
                               SET volume_ml = ?,
                                   number_of_bags = ?
                               WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_inventory);
            if (!$stmt) {
                throw new Exception("Prepare failed for update: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "dii", $remaining_ml, $remaining_bags, $inventory_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed for update: " . mysqli_stmt_error($stmt));
            }
        }

        // Add to hospital inventory with calculated ml and the blood type/group
        $insert_hospital = "INSERT INTO hospital_inventory 
                           (hospital_id, blood_type, blood_group, collection_date, expiration_date, 
                            volume_ml, number_of_bags, status, collected_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'Available', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_hospital);
        if (!$stmt) {
            error_log("Prepare failed with error: " . mysqli_error($conn));
            throw new Exception("Prepare failed for insert: " . mysqli_error($conn));
        }
        
        // Debug logs to check values before binding
        error_log("Debug - Blood Type for hospital: " . $blood_type_combined);
        error_log("Debug - Blood Group for hospital: " . $blood_group);
        
        mysqli_stmt_bind_param($stmt, "issssdis", 
            $hospital_id, 
            $blood_type_combined,  // Use the combined blood type directly
            $blood_group,          // Use the requested blood group directly
            $inventory_item['collection_date'],
            $inventory_item['expiration_date'],
            $requested_ml, 
            $number_of_bags, 
            $inventory_item['collected_by']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Insert failed with error: " . mysqli_stmt_error($stmt));
            throw new Exception("Execute failed for insert: " . mysqli_stmt_error($stmt));
        }

        // Commit transaction
        if (!mysqli_commit($conn)) {
            throw new Exception("Commit failed: " . mysqli_error($conn));
        }

        echo "Successfully reserved " . $requested_ml . "ml (" . $number_of_bags . " bags) for delivery.";

    } catch (Exception $e) {
        // Log the error
        error_log("Error in process_delivery.php: " . $e->getMessage());
        
        // Rollback transaction on error
        mysqli_rollback($conn);
        http_response_code(400);
        echo "Error: " . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Method not allowed";
}

mysqli_close($conn);
?> 