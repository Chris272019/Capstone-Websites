<?php
// Include database connection
include('connection.php');

// First, check if the database connection is working
if ($conn->connect_error) {
    die("<h3>Database connection failed:</h3> " . $conn->connect_error);
} else {
    echo "<h3>Database connection successful!</h3>";
}

// Check if the blood_collection_inventory table exists
$checkTable = $conn->query("SHOW TABLES LIKE 'blood_collection_inventory'");
if ($checkTable->num_rows == 0) {
    die("<h3>Error: The blood_collection_inventory table does not exist!</h3>");
} else {
    echo "<p>The blood_collection_inventory table exists.</p>";
}

// Check the count of records in the table
$countQuery = "SELECT COUNT(*) as total FROM blood_collection_inventory";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
echo "<p>Total records in blood_collection_inventory: " . $countRow['total'] . "</p>";

// If there are records, check if the query used in process_fetch_inventory.php is working
if ($countRow['total'] > 0) {
    $sql = "SELECT 
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
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo "<h3>Error in the query:</h3> " . $conn->error;
    } else {
        echo "<p>Query executed successfully. Found " . $result->num_rows . " records.</p>";
        
        // If query returned 0 records but there are records in the table, there might be a JOIN issue
        if ($result->num_rows == 0) {
            echo "<h3>Possible issue with the JOIN:</h3>";
            
            // Check if user_id is correctly set in the blood_collection_inventory table
            $userIdCheck = "SELECT id, user_id FROM blood_collection_inventory LIMIT 5";
            $userIdResult = $conn->query($userIdCheck);
            
            echo "<h4>Sample user_id values in blood_collection_inventory:</h4>";
            echo "<table border='1'><tr><th>ID</th><th>user_id</th></tr>";
            
            while ($row = $userIdResult->fetch_assoc()) {
                echo "<tr><td>" . $row['id'] . "</td><td>" . ($row['user_id'] ?? "NULL") . "</td></tr>";
            }
            
            echo "</table>";
            
            // Try a different query without the JOIN to see if it returns results
            $simpleQuery = "SELECT * FROM blood_collection_inventory LIMIT 5";
            $simpleResult = $conn->query($simpleQuery);
            
            echo "<h4>Records without JOIN (should show data if JOIN is the issue):</h4>";
            
            if ($simpleResult->num_rows > 0) {
                echo "<table border='1'><tr>";
                $firstRow = $simpleResult->fetch_assoc();
                
                foreach ($firstRow as $key => $value) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                
                echo "</tr><tr>";
                
                foreach ($firstRow as $value) {
                    echo "<td>" . htmlspecialchars($value ?? "NULL") . "</td>";
                }
                
                echo "</tr>";
                
                while ($row = $simpleResult->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value ?? "NULL") . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No records found even without JOIN.</p>";
            }
        }
    }
} else {
    echo "<h3>There are no records in the blood_collection_inventory table.</h3>";
    echo "<p>The 'No inventory records available at the moment.' message is correctly displayed.</p>";
}

// Close connection
$conn->close();
?> 