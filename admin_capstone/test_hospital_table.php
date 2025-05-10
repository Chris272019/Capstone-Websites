<?php
// Include database connection
include('connection.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connection successful!<br><br>";
}

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'hospital_accounts'");
if ($table_check->num_rows == 0) {
    die("Error: hospital_accounts table does not exist!");
} else {
    echo "hospital_accounts table exists!<br><br>";
}

// Count records
$count = $conn->query("SELECT COUNT(*) as total FROM hospital_accounts");
$row = $count->fetch_assoc();
echo "Number of records in hospital_accounts: " . $row['total'] . "<br><br>";

// Show table structure
echo "<h2>Table Structure</h2>";
$structure = $conn->query("DESCRIBE hospital_accounts");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Show sample data
echo "<h2>Sample Data</h2>";
$data = $conn->query("SELECT * FROM hospital_accounts LIMIT 5");
if ($data->num_rows > 0) {
    echo "<table border='1'>";
    // Get field names
    $fields = $data->fetch_fields();
    echo "<tr>";
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";
    
    // Reset result pointer
    $data->data_seek(0);
    
    // Output data
    while ($row = $data->fetch_assoc()) {
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

$conn->close();
?> 