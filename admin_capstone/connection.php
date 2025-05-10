<?php
error_reporting(0); // Suppress PHP warnings and notices
$servername = "localhost";
$username = "root"; // or your actual username
$password = "";     // or your password
$database = "bloodbank2.0"; // make sure this exists

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Instead of die() which outputs text, throw an exception that can be caught
    throw new Exception("Connection failed: " . $conn->connect_error);
}
?>

