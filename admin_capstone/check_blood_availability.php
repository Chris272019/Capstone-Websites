<?php
include('connection.php');

// Function to normalize blood type format
function normalizeBloodType($type) {
    $type = strtolower($type);
    $type = str_replace('_', ' ', $type);
    return ucwords($type);
}

// Function to check blood availability
function checkBloodAvailability($conn, $blood_type, $blood_group) {
    // Normalize the blood type for comparison
    $normalized_type = normalizeBloodType($blood_type);
    
    // Debug log
    error_log("Checking availability for: Type={$blood_type}, Group={$blood_group}");
    error_log("Normalized type: {$normalized_type}");
    
    // Check for specific blood type and group in inventory
    $sql = "SELECT SUM(volume_ml) as total_volume, SUM(number_of_bags) as total_bags, blood_type 
            FROM blood_collection_inventory 
            WHERE (LOWER(REPLACE(blood_type, '_', ' ')) = LOWER(?) OR blood_type = ?) 
            AND blood_group = ? 
            AND status = 'Available'
            GROUP BY blood_type";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        return [
            'available' => false,
            'volume' => 0,
            'bags' => 0,
            'type' => $blood_type,
            'group' => $blood_group,
            'message' => 'Error preparing inventory check'
        ];
    }
    
    $normalized_type_lower = strtolower($normalized_type);
    $stmt->bind_param("sss", $normalized_type_lower, $blood_type, $blood_group);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        error_log("Error executing statement: " . $stmt->error);
        return [
            'available' => false,
            'volume' => 0,
            'bags' => 0,
            'type' => $blood_type,
            'group' => $blood_group,
            'message' => 'Error executing inventory check'
        ];
    }
    
    $row = $result->fetch_assoc();
    $volume = $row['total_volume'] ?? 0;
    $bags = $row['total_bags'] ?? 0;
    
    // Debug log
    error_log("Found in inventory: Volume={$volume}, Bags={$bags}, Type={$row['blood_type']}");
    
    return [
        'available' => ($volume > 0 && $bags > 0),
        'volume' => $volume,
        'bags' => $bags,
        'type' => $blood_type,
        'group' => $blood_group,
        'message' => ($volume > 0 && $bags > 0) ? 
            "Blood available: {$bags} bags, {$volume}ml" : 
            "No {$blood_type} {$blood_group} available in inventory"
    ];
}

// Get the request data
$request_id = $_POST['request_id'] ?? null;
$blood_types = json_decode($_POST['blood_types'], true) ?? [];

if (!$request_id || empty($blood_types)) {
    echo json_encode([
        'available' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

// Debug log
error_log("Processing request ID: {$request_id}");
error_log("Blood types: " . print_r($blood_types, true));

// Check availability for each blood type
$blood_available = false;
$available_types = [];
$unavailable_types = [];
$messages = [];

foreach ($blood_types as $field => $info) {
    if (!empty($info['group'])) {
        $availability = checkBloodAvailability($conn, $info['type'], $info['group']);
        if ($availability['available']) {
            $blood_available = true;
            $available_types[] = [
                'type' => $info['type'],
                'group' => $info['group'],
                'volume' => $availability['volume'],
                'bags' => $availability['bags']
            ];
        } else {
            $unavailable_types[] = [
                'type' => $info['type'],
                'group' => $info['group']
            ];
        }
        $messages[] = $availability['message'];
    }
}

// Debug log
error_log("Blood available: " . ($blood_available ? 'Yes' : 'No'));
error_log("Available types: " . print_r($available_types, true));
error_log("Unavailable types: " . print_r($unavailable_types, true));

// Return the detailed result
echo json_encode([
    'available' => $blood_available,
    'available_types' => $available_types,
    'unavailable_types' => $unavailable_types,
    'messages' => $messages,
    'message' => $blood_available ? 
        'Blood is available for this request' : 
        'No matching blood available in inventory'
]);

// Close the database connection
$conn->close();
?> 