<?php
include('connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Hospital Accounts Table Structure</h2>";

// Check table structure
$result = $conn->query("DESCRIBE hospital_accounts");
if (!$result) {
    echo "Error: " . $conn->error;
} else {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<h2>Sample Data</h2>";

// Get sample data
$result = $conn->query("SELECT * FROM hospital_accounts LIMIT 5");
if (!$result) {
    echo "Error: " . $conn->error;
} else {
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        
        // Get field names
        $fields = $result->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Output data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No records found in hospital_accounts table.";
    }
}

$conn->close();
?> 