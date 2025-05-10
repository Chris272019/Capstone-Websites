<?php
// Include database connection
include('connection.php');

// Query to fetch hospital names from hospital_accounts table
$sql = "SELECT id, hospital_name as name FROM hospital_accounts ORDER BY hospital_name ASC";
$result = mysqli_query($conn, $sql);

$hospitals = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $hospitals[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($hospitals);

// Close the database connection
mysqli_close($conn);
?> 