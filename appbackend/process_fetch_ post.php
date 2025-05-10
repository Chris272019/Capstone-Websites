<?php
header('Content-Type: application/json'); // Set the content type to JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include('connection.php');

// SQL query to fetch title, description, image_path, created_at, and id from the events table
$sql = "SELECT id, title, description, image_path, created_at FROM events ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

$response = []; // Initialize an array to hold the response

if ($result) {
    // Check if there are rows returned from the database
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Format the created_at date
            $created_at = date("F j, Y, g:i a", strtotime($row['created_at']));

            // Add the row data to the response array
            $response[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'image_path' => $row['image_path'],
                'created_at' => $created_at,
            ];
        }
        // Send the response as JSON
        echo json_encode(['status' => 'success', 'posts' => $response]);
    } else {
        // No posts available
        echo json_encode(['status' => 'error', 'message' => 'No posts available']);
    }
} else {
    // Query failed, send error
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch posts from the database']);
}

mysqli_close($conn);
?>
