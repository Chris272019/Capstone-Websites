<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include('connection.php');

function handleFileUpload($file, $type) {
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/Capstone/image/";

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $file_name = uniqid() . '_' . $type . '.' . $file_extension;
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $file_name;
    }
    return "";
}

// Get data from POST request and escape special characters
$username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : '';
$surname = isset($_POST['surname']) ? $conn->real_escape_string($_POST['surname']) : '';
$firstname = isset($_POST['firstname']) ? $conn->real_escape_string($_POST['firstname']) : '';
$middlename = isset($_POST['middlename']) ? $conn->real_escape_string($_POST['middlename']) : '';
$birthdate = isset($_POST['birthdate']) ? $conn->real_escape_string($_POST['birthdate']) : '';
$age = isset($_POST['age']) ? $conn->real_escape_string($_POST['age']) : '';
$civil_status = isset($_POST['civil_status']) ? $conn->real_escape_string($_POST['civil_status']) : '';
$sex = isset($_POST['sex']) ? $conn->real_escape_string($_POST['sex']) : '';
$house_no = isset($_POST['house_no']) ? $conn->real_escape_string($_POST['house_no']) : '';
$street = isset($_POST['street']) ? $conn->real_escape_string($_POST['street']) : '';
$barangay = isset($_POST['barangay']) ? $conn->real_escape_string($_POST['barangay']) : '';
$town = isset($_POST['town']) ? $conn->real_escape_string($_POST['town']) : '';
$province = isset($_POST['province']) ? $conn->real_escape_string($_POST['province']) : '';
$zipcode = isset($_POST['zipcode']) ? $conn->real_escape_string($_POST['zipcode']) : '';
$office_address = isset($_POST['office_address']) ? $conn->real_escape_string($_POST['office_address']) : '';
$nationality = isset($_POST['nationality']) ? $conn->real_escape_string($_POST['nationality']) : '';
$religion = isset($_POST['religion']) ? $conn->real_escape_string($_POST['religion']) : '';
$education = isset($_POST['education']) ? $conn->real_escape_string($_POST['education']) : '';
$occupation = isset($_POST['occupation']) ? $conn->real_escape_string($_POST['occupation']) : '';
$telephone_number = isset($_POST['telephone_number']) ? $conn->real_escape_string($_POST['telephone_number']) : '';
$mobile_number = isset($_POST['mobile_number']) ? $conn->real_escape_string($_POST['mobile_number']) : '';
$blood_group = isset($_POST['blood_type']) ? $conn->real_escape_string($_POST['blood_type']) : null;

// Debugging: Log received values
error_log("Received Blood Type: " . $blood_group);

// Handle file uploads
$school_identification = isset($_FILES['school_identification']) ? handleFileUpload($_FILES['school_identification'], 'school') : '';
$company_identification = isset($_FILES['company_identification']) ? handleFileUpload($_FILES['company_identification'], 'company') : '';
$prc_identification = isset($_FILES['prc_identification']) ? handleFileUpload($_FILES['prc_identification'], 'prc') : '';
$drivers_identification = isset($_FILES['drivers_identification']) ? handleFileUpload($_FILES['drivers_identification'], 'drivers') : '';
$sss_gsis_bir = isset($_FILES['sss_gsis_bir']) ? handleFileUpload($_FILES['sss_gsis_bir'], 'sss') : '';

// Construct SQL query with proper escaping
$sql = "UPDATE users SET
        surname='$surname',
        firstname='$firstname',
        middlename='$middlename',
        birthdate='$birthdate',
        age='$age',
        civil_status='$civil_status',
        sex='$sex',
        house_no='$house_no',
        street='$street',
        barangay='$barangay',
        town='$town',
        province='$province',
        zipcode='$zipcode',
        office_address='$office_address',
        nationality='$nationality',
        religion='$religion',
        education='$education',
        occupation='$occupation',
        telephone_number='$telephone_number',
        mobile_number='$mobile_number',
        blood_group=" . ($blood_group ? "'$blood_group'" : "NULL");

// Only update file paths if a new file was uploaded
if ($school_identification) $sql .= ", school_identification='$school_identification'";
if ($company_identification) $sql .= ", company_identification='$company_identification'";
if ($prc_identification) $sql .= ", prc_identification='$prc_identification'";
if ($drivers_identification) $sql .= ", drivers_identification='$drivers_identification'";
if ($sss_gsis_bir) $sql .= ", sss_gsis_bir='$sss_gsis_bir'";

$sql .= " WHERE username='$username'";

// Debugging: Log the final SQL query
error_log("SQL Query: " . $sql);

// Execute SQL query and check for errors
if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true, "message" => "User information updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    error_log("SQL Error: " . $conn->error);
}

$conn->close();
?>
