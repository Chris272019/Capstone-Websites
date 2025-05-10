<?php
// Include database connection
include('connection.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, check if the database connection is working
if ($conn->connect_error) {
    die("<h3>Database connection failed:</h3> " . $conn->connect_error);
} else {
    echo "<h3>Database connection successful!</h3>";
}

// Check if the staff_account table exists
$checkTable = $conn->query("SHOW TABLES LIKE 'staff_account'");
if ($checkTable->num_rows == 0) {
    die("<h3>Error: The staff_account table does not exist!</h3>");
} else {
    echo "<p>The staff_account table exists.</p>";
}

// Show table structure
echo "<h3>Table Structure:</h3>";
$structure = $conn->query("DESCRIBE staff_account");
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
echo "<h3>Sample Data:</h3>";
$data = $conn->query("SELECT * FROM staff_account LIMIT 5");
echo "<table border='1'>";
if ($data->num_rows > 0) {
    // Get column names
    $fields = $data->fetch_fields();
    echo "<tr>";
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";
    
    // Reset pointer
    $data->data_seek(0);
    
    // Show data
    while ($row = $data->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No data found in staff_account table</td></tr>";
}
echo "</table>";

// Close connection
$conn->close();
?> 