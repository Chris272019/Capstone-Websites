<?php
$servername = "localhost";
$username = "root"; // or your actual username
$password = "";     // or your password
$database = "bloodbank2.0"; // make sure this exists

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>