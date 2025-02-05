<?php
// Replace with your actual database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bloodbank2.0";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get data from POST request
$username = $_POST['username']; // Get the username from the request
$surname = $_POST['surname'];
$firstname = $_POST['firstname'];
$middlename = $_POST['middlename'];
$birthdate = $_POST['birthdate'];
$age = $_POST['age'];
$civil_status = $_POST['civil_status'];
$sex = $_POST['sex'];
$house_no = $_POST['house_no'];
$street = $_POST['street'];
$barangay = $_POST['barangay'];
$town = $_POST['town'];
$province = $_POST['province'];
$zipcode = $_POST['zipcode'];
$office_address = $_POST['office_address'];
$nationality = $_POST['nationality'];
$religion = $_POST['religion'];
$education = $_POST['education'];
$occupation = $_POST['occupation'];
$telephone_number = $_POST['telephone_number'];
$mobile_number = $_POST['mobile_number'];
$school_identification = $_POST['school_identification'];
$company_identification = $_POST['company_identification'];
$prc_identification = $_POST['prc_identification'];
$drivers_identification = $_POST['drivers_identification'];
$sss_gsis_bir = $_POST['sss_gsis_bir'];

// Update query
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
        school_identification='$school_identification',
        company_identification='$company_identification',
        prc_identification='$prc_identification',
        drivers_identification='$drivers_identification',
        sss_gsis_bir='$sss_gsis_bir'
        WHERE username='$username'"; // Use the username to identify the user

if ($conn->query($sql) === TRUE) {
    echo "User information updated successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
