<?php
// Include the database connection file
include('connection.php');

// Check if form data is received via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and sanitize it to prevent SQL injection
    $hospital_name = mysqli_real_escape_string($conn, $_POST['hospital_name']);
    $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email_address = mysqli_real_escape_string($conn, $_POST['email_address']);
    $hospital_address = mysqli_real_escape_string($conn, $_POST['hospital_address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $zip_code = mysqli_real_escape_string($conn, $_POST['zip_code']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Optional: Hash the password before storing (for security)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert new hospital account into the hospital_accounts table
    $sql = "INSERT INTO hospital_accounts (hospital_name, contact_person, contact_number, email_address, hospital_address, city, province, zip_code, password)
            VALUES ('$hospital_name', '$contact_person', '$contact_number', '$email_address', '$hospital_address', '$city', '$province', '$zip_code', '$hashed_password')";

    // Execute the query and check for success or failure
    if (mysqli_query($conn, $sql)) {
        // Success: Set success message
        $success_message = 'Hospital added successfully';
    } else {
        // Error: Set error message
        $error_message = 'There was an issue adding the hospital: ' . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
