<?php
// Include database connection
include('connection.php');

// Query to get table structure
$sql = "DESCRIBE blood_collection_inventory";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Structure of blood_collection_inventory table:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ? $row['Default'] : 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Check for sample data
    $sql2 = "SELECT * FROM blood_collection_inventory LIMIT 5";
    $result2 = mysqli_query($conn, $sql2);
    
    if ($result2 && mysqli_num_rows($result2) > 0) {
        echo "<h3>Sample data from blood_collection_inventory table:</h3>";
        echo "<table border='1'>";
        
        // Print header
        $firstRow = mysqli_fetch_assoc($result2);
        echo "<tr>";
        foreach ($firstRow as $key => $value) {
            echo "<th>" . $key . "</th>";
        }
        echo "</tr>";
        
        // Print first row
        echo "<tr>";
        foreach ($firstRow as $key => $value) {
            echo "<td>" . ($value ? htmlspecialchars($value) : 'NULL') . "</td>";
        }
        echo "</tr>";
        
        // Print remaining rows
        while ($row = mysqli_fetch_assoc($result2)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . ($value ? htmlspecialchars($value) : 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No sample data available in the blood_collection_inventory table.</p>";
    }
    
    // Debug the actual query from process_fetch_inventory.php
    $sql3 = "SELECT 
            i.id,
            u.firstname, 
            u.surname,
            i.blood_type,
            i.collection_date,
            i.expiration_date,
            i.volume_ml,
            i.status,
            i.collected_by,
            i.created_at,
            i.blood_group,
            i.number_of_bags
        FROM 
            blood_collection_inventory i
        LEFT JOIN 
            users u ON i.user_id = u.id
        ORDER BY 
            i.collection_date DESC";
    
    $result3 = mysqli_query($conn, $sql3);
    echo "<h3>Results from the actual query:</h3>";
    echo "<p>Number of records: " . mysqli_num_rows($result3) . "</p>";
    
} else {
    echo "Error executing query: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?> 