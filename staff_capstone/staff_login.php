<?php
session_start();
include('connection.php'); // Include your database connection file

// Check if the form is submitted
if (isset($_POST['login'])) {
    $staff_id = $_POST['staff_id']; // Get the staff ID from the form

    // Prepare a statement to query the database for the staff ID
    $stmt = $conn->prepare("SELECT * FROM staff_account WHERE id = ?"); 
    $stmt->bind_param("s", $staff_id);  // "s" means the parameter is a string (use "i" if it's an integer)
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If staff ID exists, fetch the staff data
        $staff = $result->fetch_assoc();

        // Store the staff ID and other details in session variables
        $_SESSION['staff_id'] = $staff['id'];  // Store the staff ID in session
        $_SESSION['staff_name'] = $staff['staff_name'];  // Store staff name in session
        $_SESSION['role'] = $staff['role'];  // Store staff role in session
        
        // Redirect based on the staff's role
        if ($staff['role'] == 'Interviewer') {
            header("Location: initial_patient.php");
            exit(); // Ensure to exit after header redirect
        } elseif ($staff['role'] == 'Physician') {
            header("Location: physical_examination_patient.php");
            exit(); // Ensure to exit after header redirect
        } elseif ($staff['role'] == 'Phlebotomist') {
            header("Location: blood_collection_patient.php");
            exit(); // Ensure to exit after header redirect
        } else {
            // If role is not recognized, handle it here
            $error_message = "Invalid staff role!";
        }
    } else {
        // If no staff is found with the entered ID
        $error_message = "Invalid staff ID!";
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Staff Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('https://www.w3schools.com/w3images/forestbridge.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        h2 {
            color: #d71c1c; /* Blood red color */
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background-color: #d71c1c; /* Blood red color */
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #b01616; /* Darker red */
        }
        
        .error-message {
            color: red;
            font-size: 16px;
            margin: 10px 0;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Blood Donation Staff Login</h2>

        <?php if (isset($error_message)) { echo "<p class='error-message'>$error_message</p>"; } ?>

        <form method="POST" action="staff_login.php">
            <label for="staff_id">Staff ID</label>
            <input type="text" id="staff_id" name="staff_id" placeholder="Enter your Staff ID" required>
            
            <button type="submit" name="login">Login</button>
        </form>
        
        <div class="footer">
            <p>&copy; 2024 Blood Donation System | All rights reserved</p>
        </div>
    </div>

</body>
</html>
