<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventory_id = isset($_POST['inventory_id']) ? intval($_POST['inventory_id']) : 0;
    $admin_id = isset($_POST['admin_id']) ? $conn->real_escape_string($_POST['admin_id']) : '';
    $blood_volume = isset($_POST['blood_volume']) ? floatval($_POST['blood_volume']) : 0;

    if ($inventory_id <= 0 || empty($admin_id) || $blood_volume <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }

    // Fetch inventory details for the given inventory_id
    $sql = "SELECT hospital_id, blood_type, blood_group, collection_date, expiration_date, number_of_bags, volume_ml 
            FROM hospital_inventory WHERE id = $inventory_id LIMIT 1";
    $result = $conn->query($sql);

    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Inventory item not found']);
        exit;
    }

    $row = $result->fetch_assoc();

    $hospital_id = intval($row['hospital_id']);
    $blood_type = $conn->real_escape_string($row['blood_type']);
    $blood_group = $conn->real_escape_string($row['blood_group']);
    $collection_date = $conn->real_escape_string($row['collection_date']);
    $expiration_date = $conn->real_escape_string($row['expiration_date']);
    $number_of_bags = intval($row['number_of_bags']);
    $current_volume_ml = floatval($row['volume_ml']);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;

    // Validate admin_id exists in users table
    // Validation removed due to unknown users table or external user management
    // $check_user_sql = "SELECT id FROM users WHERE id = ?";
    // $stmt = $conn->prepare($check_user_sql);
    // $stmt->bind_param("i", $admin_id);
    // $stmt->execute();
    // $stmt->store_result();
    // if ($stmt->num_rows === 0) {
    //     echo json_encode(['success' => false, 'message' => 'Invalid admin_id: user does not exist']);
    //     exit;
    // }
    // $stmt->close();

    $status = 'Available'; // Set status as Available as per user request

    // Insert into blood_collection_inventory
    $insert_sql = "INSERT INTO blood_collection_inventory 
        (hospital_id, blood_type, blood_group, collection_date, expiration_date, volume_ml, number_of_bags, status, user_id, created_at, updated_at)
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("issssdisiss", 
        $hospital_id, 
        $blood_type, 
        $blood_group, 
        $collection_date, 
        $expiration_date, 
        $blood_volume, 
        $number_of_bags, 
        $status, 
        $admin_id, 
        $created_at, 
        $updated_at
    );

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error inserting delivery record: ' . $stmt->error]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Calculate number of bags delivered based on volume
    // Assuming volume per bag is current_volume_ml / number_of_bags
    if ($number_of_bags > 0) {
        $volume_per_bag = $current_volume_ml / $number_of_bags;
    } else {
        $volume_per_bag = 0;
    }
    $bags_delivered = 0;
    if ($volume_per_bag > 0) {
        $bags_delivered = ceil($blood_volume / $volume_per_bag);
    }

    // Update hospital_inventory by subtracting delivered volume and bags
    $new_volume = $current_volume_ml - $blood_volume;
    $new_bags = $number_of_bags - $bags_delivered;

    if ($new_volume <= 0 || $new_bags <= 0) {
        // Delete the inventory record
        $delete_sql = "DELETE FROM hospital_inventory WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $inventory_id);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error deleting inventory record: ' . $stmt->error]);
            $stmt->close();
            exit;
        }
        $stmt->close();
    } else {
        // Update the inventory record
        $update_sql = "UPDATE hospital_inventory SET volume_ml = ?, number_of_bags = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("dii", $new_volume, $new_bags, $inventory_id);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error updating inventory record: ' . $stmt->error]);
            $stmt->close();
            exit;
        }
        $stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Blood delivery recorded successfully and inventory updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
