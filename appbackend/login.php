<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'connection.php'; 
session_start();

header("Content-Type: application/json");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Missing username or password"]);
    exit();
}

// Prepare the SQL query to check if the username exists and get verification_status
$sql = "SELECT id, username, password, surname, firstname, verification_status FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Check verification status
    $verificationStatus = $user['verification_status'];

    if ($verificationStatus == 'Pending' || $verificationStatus == 'Rejected') {
        echo json_encode([
            "status" => "error",
            "message" => "Your account is currently $verificationStatus. Please wait for approval.",
            "user_status" => $verificationStatus
        ]);
        exit();
    }

    // Verify the hashed password
    if (password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];

        $hasPersonalInfo = !empty($user['surname']) && !empty($user['firstname']);

        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user_id" => (int) $user["id"],
            "user_status" => $verificationStatus, // ✅ Include user status here
            "hasPersonalInfo" => $hasPersonalInfo
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid username"]);
}

$stmt->close();
$conn->close();
exit();

?>
