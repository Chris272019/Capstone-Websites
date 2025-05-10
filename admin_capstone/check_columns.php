<?php
// Include database connection
include('connection.php');

// Check the actual columns in the table
$columnsQuery = "SHOW COLUMNS FROM blood_collection_inventory";
$columnsResult = $conn->query($columnsQuery);

if (!$columnsResult) {
    die("Error fetching columns: " . $conn->error);
}

echo "<h3>Columns in blood_collection_inventory table:</h3>";
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = $columnsResult->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] !== null ? $row['Default'] : "NULL") . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Try a direct query without the problematic column
$simpleSql = "SELECT * FROM blood_collection_inventory LIMIT 1";
$simpleResult = $conn->query($simpleSql);

if (!$simpleResult) {
    die("Error executing simple query: " . $conn->error);
}

echo "<h3>Sample data from blood_collection_inventory:</h3>";
if ($simpleResult->num_rows > 0) {
    $row = $simpleResult->fetch_assoc();
    echo "<table border='1'><tr>";
    
    foreach ($row as $key => $value) {
        echo "<th>" . htmlspecialchars($key) . "</th>";
    }
    
    echo "</tr><tr>";
    
    foreach ($row as $value) {
        echo "<td>" . htmlspecialchars($value ?? "NULL") . "</td>";
    }
    
    echo "</tr></table>";
} else {
    echo "<p>No data found.</p>";
}

// Close connection
$conn->close();
?> 