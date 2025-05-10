<?php
include('connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Modifying Hospital Inventory Table</h2>";

// First check the current structure of the blood_group column
$result = $conn->query("SHOW COLUMNS FROM hospital_inventory LIKE 'blood_group'");
if (!$result) {
    echo "Error querying column: " . $conn->error;
} else {
    $row = $result->fetch_assoc();
    echo "<p>Current blood_group column type: " . htmlspecialchars($row['Type']) . "</p>";
}

// Modify the blood_group column to use VARCHAR to support more types
$alter_query = "ALTER TABLE hospital_inventory MODIFY blood_group VARCHAR(50) NOT NULL";
if ($conn->query($alter_query)) {
    echo "<p>Successfully altered blood_group column to VARCHAR(50)</p>";
} else {
    echo "<p>Error altering blood_group column: " . $conn->error . "</p>";
}

// Check the modified structure
$result = $conn->query("SHOW COLUMNS FROM hospital_inventory LIKE 'blood_group'");
if (!$result) {
    echo "Error querying column after modification: " . $conn->error;
} else {
    $row = $result->fetch_assoc();
    echo "<p>New blood_group column type: " . htmlspecialchars($row['Type']) . "</p>";
}

// Try inserting a record with leukocyte_poor_fresh_frozen_plasma blood group
echo "<h2>Test Insert with New Blood Group Value</h2>";

try {
    $sql = "INSERT INTO hospital_inventory 
           (hospital_id, blood_type, blood_group, collection_date, expiration_date, 
            volume_ml, number_of_bags, status, collected_by)
           VALUES (1, 'A', 'leukocyte_poor_fresh_frozen_plasma', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 42 DAY), 
                  450, 1, 'Available', 'Test')";
    
    if ($conn->query($sql)) {
        $insert_id = $conn->insert_id;
        echo "Test insert successful! New ID: $insert_id";
    } else {
        echo "Test insert failed: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 