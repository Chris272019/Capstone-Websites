<?php
header("Content-Type: application/json");
include 'connection.php'; // Ensure your database connection file is included

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
    exit();
}

$user_id = $data['user_id'];

$query = "SELECT surname, firstname, middlename, birthdate, age, civil_status, sex, house_no, street, barangay, town, province, 
                 zipcode, office_address, nationality, religion, education, occupation, telephone_number, mobile_number, blood_group 
          FROM users WHERE id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $user]);
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
