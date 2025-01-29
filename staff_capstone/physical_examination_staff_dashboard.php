<?php
// Start the session (optional, if you are using sessions)
session_start();

// Assuming a connection is established to your database
include('connection.php');

// Retrieve the user_id from the URL query string (example: update.php?user_id=123)
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} else {
    $user_id = "No user ID provided"; // In case no user_id is passed
}

// Get the staff_id from session (assuming the staff_id is stored in session after login)
if (isset($_SESSION['staff_id'])) {
    $staff_id = $_SESSION['staff_id']; // This value is assumed to be stored when the user logs in
} else {
    $staff_id = null; // Handle the case where the staff_id is not set (if needed)
}

// Handle form submission (when "Update Physical Examination" button is clicked)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_pressure = $_POST['blood_pressure'];
    $pulse_rate = $_POST['pulse_rate'];
    $body_temp = $_POST['body_temp'];
    $gen_appearance = $_POST['gen_appearance'];
    $skin = $_POST['skin'];
    $heent = $_POST['heent'];
    $heart_lungs = $_POST['heart_lungs'];
    $remarks = $_POST['remarks'];
    $reason = $_POST['reason'];
    $bags_used = $_POST['bags_used'];
    
    // Update the physical_examination table with the form data and set status to 'Verified'
    $update_query = "UPDATE physical_examination SET
        blood_pressure = ?, pulse_rate = ?, body_temp = ?, gen_appearance = ?, skin = ?,
        heent = ?, heart_lungs = ?, remarks = ?, reason = ?, bags_used = ?, staff_id = ?, status = 'Verified' WHERE user_id = ?";

    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("sssssssssssi", $blood_pressure, $pulse_rate, $body_temp, $gen_appearance, $skin,
            $heent, $heart_lungs, $remarks, $reason, $bags_used, $staff_id, $user_id);

        if ($stmt->execute()) {
            // After successful update, insert user_id into the blood_collection table
            $insert_blood_collection_query = "INSERT INTO blood_collection (user_id) VALUES (?)";
            if ($stmt_blood_collection = $conn->prepare($insert_blood_collection_query)) {
                $stmt_blood_collection->bind_param("i", $user_id);
                $stmt_blood_collection->execute();
                $stmt_blood_collection->close();
            }

            // Redirect to physical_examination_patient.php after updating and inserting
            header("Location: physical_examination_patient.php?user_id=" . $user_id);
            exit();
        } else {
            $error_message = "Error updating physical examination!";
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Physical Examination</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
        }

        input, select, textarea {
            margin-top: 5px;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
        }

        button {
            margin-top: 20px;
            padding: 10px 15px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 15px;
            font-size: 16px;
            color: green;
            text-align: center;
        }

        .error {
            color: red;
        }

        .reason-container {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Physical Examination</h1>

        <!-- Display success or error message -->
        <?php if (isset($message)) { ?>
            <p class="message"><?php echo $message; ?></p>
        <?php } elseif (isset($error_message)) { ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php } ?>

        <form method="POST">
            <!-- Blood Pressure -->
            <label for="blood_pressure">Blood Pressure</label>
            <input type="text" id="blood_pressure" name="blood_pressure" required>

            <!-- Pulse Rate -->
            <label for="pulse_rate">Pulse Rate</label>
            <input type="text" id="pulse_rate" name="pulse_rate" required>

            <!-- Body Temperature -->
            <label for="body_temp">Body Temperature</label>
            <input type="text" id="body_temp" name="body_temp" required>

            <!-- General Appearance -->
            <label for="gen_appearance">General Appearance</label>
            <input type="text" id="gen_appearance" name="gen_appearance" required>

            <!-- Skin -->
            <label for="skin">Skin</label>
            <input type="text" id="skin" name="skin" required>

            <!-- HEENT -->
            <label for="heent">HEENT</label>
            <input type="text" id="heent" name="heent" required>

            <!-- Heart and Lungs -->
            <label for="heart_lungs">Heart and Lungs</label>
            <input type="text" id="heart_lungs" name="heart_lungs" required>

            <!-- Remarks -->
            <label for="remarks">Remarks</label>
            <select id="remarks" name="remarks" required>
                <option value="Accepted">Accepted</option>
                <option value="Temporary Deferred">Temporary Deferred</option>
                <option value="Permanently Deferred">Permanently Deferred</option>
                <option value="Refused">Refused</option>
            </select>

            <!-- Reason (conditionally visible) -->
            <div class="reason-container" id="reason-container">
    <label for="reason">Reason</label>
    <textarea id="reason" name="reason" rows="4"></textarea>
</div>


            <!-- Bags Used -->
            <label for="bags_used">Bags Used</label>
            <select id="bags_used" name="bags_used" required>
                <option value="single">Single</option>
                <option value="multiple">Multiple</option>
                <option value="top&bottom apheresis">Top & Bottom Apheresis</option>
            </select>

            <button type="submit">Update Physical Examination</button>
        </form>
    </div>

    <script>
        // Get the elements
        const remarksSelect = document.getElementById('remarks');
        const reasonContainer = document.getElementById('reason-container');

        // Function to toggle the "Reason" field based on the selected remarks
        function toggleReasonField() {
            const selectedValue = remarksSelect.value;
            if (selectedValue === 'Temporary Deferred' || selectedValue === 'Permanently Deferred' || selectedValue === 'Refused') {
                reasonContainer.style.display = 'block'; // Show the Reason textarea
            } else {
                reasonContainer.style.display = 'none'; // Hide the Reason textarea
            }
        }

        // Initial check on page load
        toggleReasonField();

        // Add event listener for change on the remarks dropdown
        remarksSelect.addEventListener('change', toggleReasonField);
    </script>
</body>
</html>
