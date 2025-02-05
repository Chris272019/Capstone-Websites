<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection
$servername = "localhost"; // Replace with your database host
$username = "root";        // Replace with your database username
$password = "";            // Replace with your database password
$dbname = "bloodbank2.0";     // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

// Retrieve POST data
$data = json_decode(file_get_contents("php://input"), true);
$admin_id = $data['username'] ?? null;
$password_input = $data['password'] ?? null;

if (!$admin_id || !$password_input) {
    echo json_encode(["status" => "error", "message" => "Admin ID and password are required"]);
    exit();
}

// Query the database (check for username and password)
$sql = "SELECT * FROM admin_login WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $admin_id, $password_input);  // Bind the input parameters
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Login successful"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Admin ID or password"]);
}

$stmt->close();
$conn->close();
?>
