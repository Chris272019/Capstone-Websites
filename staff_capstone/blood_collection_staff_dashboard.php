<?php
// Start the session
session_start();

// Include the database connection
include('connection.php');

// Get the logged-in staff ID
if (!isset($_SESSION['staff_id'])) {
    echo "<p>Error: Staff ID not found in session.</p>";
    exit();
}
$staff_id = $_SESSION['staff_id'];

// Get the user ID from the URL
if (!isset($_GET['user_id'])) {
    echo "<p>Error: User ID not provided.</p>";
    exit();
}
$user_id = $_GET['user_id'];

// Fetch existing blood collection record
$query = "SELECT * FROM blood_collection WHERE user_id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $karmi = $row['karmi'];
        $terumo = $row['terumo'];
        $special_bag = $row['special_bag'];
        $apheresis = $row['apheresis'];
        $amount_blood_taken = $row['amount_blood_taken'];
        $successful = $row['successful'];
        $donors_reaction = $row['donors_reaction'];
        $management_done = $row['management_done'];
        $start_time = $row['start_time'];
        $end_time = $row['end_time'];
        $status = $row['status'];
        $blood_type = $row['blood_type'];
    } else {
        echo "<p>No record found for this user.</p>";
        exit();
    }
    $stmt->close();
} else {
    echo "<p>Error preparing the query: " . $conn->error . "</p>";
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form values
    $karmi = $_POST['karmi'];
    $terumo = $_POST['terumo'];
    $special_bag = $_POST['special_bag'];
    $apheresis = $_POST['apheresis'];
    $amount_blood_taken = $_POST['amount_blood_taken'];
    $successful = $_POST['successful'];
    $donors_reaction = $_POST['donors_reaction'];
    $management_done = $_POST['management_done'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $blood_type = $_POST['blood_type']; // Retain for other tables
    $blood_type_plasma = $_POST['blood_type']; // Insert into blood_collection_inventory

    // Debugging: Check if blood type is captured
    if (empty($blood_type)) {
        echo "<p>Error: Blood type is missing from form submission.</p>";
        exit();
    }

    // Set status to "Verified"
    $status = 'Verified';

    // Update blood_collection
    $update_query = "UPDATE blood_collection SET karmi = ?, terumo = ?, special_bag = ?, apheresis = ?, amount_blood_taken = ?, successful = ?, donors_reaction = ?, management_done = ?, start_time = ?, end_time = ?, status = ?, staff_id = ?, blood_type = ? WHERE user_id = ?";
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("sssssssssssssi", $karmi, $terumo, $special_bag, $apheresis, $amount_blood_taken, $successful, $donors_reaction, $management_done, $start_time, $end_time, $status, $staff_id, $blood_type, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<p>Error updating blood_collection: " . $conn->error . "</p>";
        exit();
    }

    // Update blood_donation_history
    $history_query = "UPDATE blood_donation_history SET donation_quantity = ?, blood_type = ? WHERE user_id = ?";
    if ($stmt = $conn->prepare($history_query)) {
        $stmt->bind_param("dsi", $amount_blood_taken, $blood_type, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<p>Error updating blood_donation_history: " . $conn->error . "</p>";
        exit();
    }

    // Insert into blood_collection_inventory with blood_type_plasma
    $collection_date = date("Y-m-d H:i:s");
    $expiration_date = date("Y-m-d H:i:s", strtotime("+42 days"));
    $status = "Available";

    $insert_query = "INSERT INTO blood_collection_inventory (user_id, plasma_type, collection_date, expiration_date, volume_ml, status, collected_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    if ($stmt = $conn->prepare($insert_query)) {
        $stmt->bind_param("isssiss", $user_id, $blood_type_plasma, $collection_date, $expiration_date, $amount_blood_taken, $status, $staff_id);
        
        // Debugging: Check values before execution
        echo "<p>Inserting: user_id=$user_id, plasma_type=$blood_type_plasma, collection_date=$collection_date, expiration_date=$expiration_date, volume_ml=$amount_blood_taken, status=$status, collected_by=$staff_id</p>";

        if (!$stmt->execute()) {
            echo "<p>Error executing blood_collection_inventory insert: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Error preparing blood_collection_inventory insert: " . $conn->error . "</p>";
        exit();
    }

    // Redirect after successful updates
    header("Location: blood_collection_patient.php");
    exit();
}

// Close database connection
$conn->close();
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Blood Collection</title>
    <style>
        /* Same styles as before */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #b30000;
        }
        label {
            font-size: 14px;
            margin-top: 10px;
        }
        select, input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        textarea {
            resize: vertical;
        }
        .submit-btn {
            background-color: #b30000;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .submit-btn:hover {
            background-color: #990000;
        }
        .radio-container {
            display: none; /* Initially hidden */
            margin-top: 10px;   
        }

    </style>
</head>
<body>

<div class="container">
    <h1>Submit Blood Collection Information</h1>

    <!-- Form to submit the blood collection details -->
    <form method="POST">
        <label for="karmi">Karmi:</label>
        <select name="karmi" id="karmi" required>
            <option value="NoneS">None</option>
            <option value="S">S</option>
            <option value="D">D</option>
            <option value="T">T</option>
            <option value="Q">Q</option>
        </select><br><br>

        <label for="terumo">Terumo:</label>
        <select name="terumo" id="terumo" required>
            <option value="None">None</option>
            <option value="S">S</option>
            <option value="D">D</option>
            <option value="T">T</option>
            <option value="Q">Q</option>
        </select><br><br>

        <label for="special_bag">Special Bag:</label>
        <select name="special_bag" id="special_bag" required>
            <option value="None">None</option>
            <option value="fk">FK</option>
            <option value="t&b">T&B</option>
        </select><br><br>

        <label for="apheresis">Apheresis:</label>
        <select name="apheresis" id="apheresis" required>
            <option value="none">None</option>
            <option value="fres">Fres</option>
            <option value="ami">Ami</option>
            <option value="hae">Hae</option>
            <option value="tri">Tri</option>
        </select><br><br>

        <!-- Replacing the radio buttons with a dropdown for blood components -->
        <label for="blood_type">Blood Type:</label>
<select name="blood_type" id="blood_type" required>
    <option value="None">None</option>
    <option value="whole_blood">Whole Blood</option>
    <option value="packed_rbc">Packed RBC</option>
    <option value="washed_rbc">Washed RBC</option>
    <option value="buffy_coat_poor_rbc">Buffy Coat Poor RBC</option>
    <option value="platelet_concentrate">Platelet Concentrate</option>
    <option value="apheresis_platelet">Apheresis Platelet</option>
    <option value="leukocyte_poor_platelet_concentrate">Leukocyte Poor Platelet Concentrate</option>
    <option value="fresh_frozen_plasma">Fresh Frozen Plasma</option>
    <option value="leukocyte_poor_fresh_frozen_plasma">Leukocyte Poor Fresh Frozen Plasma</option>
    <option value="cryoprecipitate">Cryoprecipitate</option>
</select><br><br>


        <label for="amount_blood_taken">Amount of Blood Taken in Units:</label>
        <input type="text" name="amount_blood_taken" id="amount_blood_taken" required><br><br>

        <label for="successful">Successful:</label>
        <select name="successful" id="successful" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br><br>

        <label for="donors_reaction">Donor's Reaction:</label><br>
        <textarea name="donors_reaction" id="donors_reaction" rows="4" required></textarea><br><br>

        <label for="management_done">Management Done:</label>
        <input type="text" name="management_done" id="management_done" required><br><br>

        <label for="start_time">Start Time:</label>
        <input type="datetime-local" name="start_time" id="start_time" required><br><br>

        <label for="end_time">End Time:</label>
        <input type="datetime-local" name="end_time" id="end_time" required><br><br>

        <!-- Removed status field as it's set automatically to "Verified" -->
        <input type="submit" value="Submit" class="submit-btn">
    </form>
</div>

</body>
</html>