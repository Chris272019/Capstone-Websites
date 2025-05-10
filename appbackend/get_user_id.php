<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'connection.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];

    // Query to get the id based on username
    $query = "SELECT id FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            echo json_encode(['status' => 'success', 'id' => $row['id']]); // Return the id
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute query']);
    }
}
?>
