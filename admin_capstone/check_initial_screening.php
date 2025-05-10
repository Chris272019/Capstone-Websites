<?php
include('connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Initial Screening Table Structure</h2>";

// Check table structure
$result = $conn->query("DESCRIBE initial_screening");
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
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Get sample data
$result = $conn->query("SELECT * FROM initial_screening LIMIT 1");
if (!$result) {
    echo "Error getting sample data: " . $conn->error;
} else {
    echo "<h2>Sample Data</h2>";
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<table border='1'>";
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
        echo "</table>";
    } else {
        echo "No data found in the initial_screening table.";
    }
}

$conn->close();
?> 