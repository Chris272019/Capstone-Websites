<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// fetch_verified_ids.php
include('connection.php');

// Query to fetch only IDs from the screening_answers table with Verified status
$query = "SELECT id 
          FROM screening_answers 
          WHERE verification_status = 'Verified'";

$result = mysqli_query($conn, $query);

$ids = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['id']; // Add each ID to the array
    }
    echo json_encode(['status' => 'success', 'data' => $ids]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No verified entries found']);
}
?>
