<?php
header("Content-Type: application/json");
include 'connection.php'; // Ensure this file contains a valid database connection

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
    exit();
}

$user_id = $data['user_id'];
$surname = $data['surname'] ?? '';
$firstname = $data['firstname'] ?? '';
$middlename = $data['middlename'] ?? '';
$birthdate = $data['birthdate'] ?? '';
$age = $data['age'] ?? null;
$civil_status = $data['civil_status'] ?? '';
$sex = $data['sex'] ?? '';
$house_no = $data['house_no'] ?? '';
$street = $data['street'] ?? '';
$barangay = $data['barangay'] ?? '';
$town = $data['town'] ?? '';
$province = $data['province'] ?? '';
$zipcode = $data['zipcode'] ?? '';
$office_address = $data['office_address'] ?? '';
$nationality = $data['nationality'] ?? '';
$religion = $data['religion'] ?? '';
$education = $data['education'] ?? '';
$occupation = $data['occupation'] ?? '';
$telephone_number = $data['telephone_number'] ?? '';
$mobile_number = $data['mobile_number'] ?? '';
$blood_group = $data['blood_group'] ?? '';

// SQL query to update user data
$query = "UPDATE users SET 
            surname = ?, firstname = ?, middlename = ?, birthdate = ?, age = ?, civil_status = ?, 
            sex = ?, house_no = ?, street = ?, barangay = ?, town = ?, province = ?, zipcode = ?, 
            office_address = ?, nationality = ?, religion = ?, education = ?, occupation = ?, 
            telephone_number = ?, mobile_number = ?, blood_group = ? 
          WHERE id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]);
    exit();
}

$stmt->bind_param(
    "ssssissssssssssssssssi",
    $surname, $firstname, $middlename, $birthdate, $age, $civil_status, 
    $sex, $house_no, $street, $barangay, $town, $province, $zipcode, 
    $office_address, $nationality, $religion, $education, $occupation, 
    $telephone_number, $mobile_number, $blood_group, $user_id
);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "User data updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
