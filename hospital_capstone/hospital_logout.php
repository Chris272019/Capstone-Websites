<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page or home page
header("Location: hospital_login.php");
exit;
?>
