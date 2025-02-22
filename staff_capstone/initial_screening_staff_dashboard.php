<?php
// Start the session
session_start();

// Check if the staff is logged in
if (!isset($_SESSION['staff_id'])) {
    echo "You are not logged in!";
    exit;
}

// Get the logged-in staff's ID from the session
$staff_id = $_SESSION['staff_id'];

// Include database connection
include('connection.php');

// Retrieve the user_id from the URL query string
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} else {
    echo "No user ID provided!";
    exit;
}

// Fetch existing data for the user_id
// Fetch existing data for the user_id
$query = "SELECT * FROM initial_screening WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Assign fetched data to variables
    $body_weight = $row['body_weight'];
    $sp_gr = $row['sp_gr'];
    $hgb = $row['hgb'];
    $rbc = $row['rbc'];
    $wbc = $row['wbc'];
    $plt_count = $row['plt_count'];
    $blood_type = $row['blood_type'];
    $type_of_donation = $row['type_of_donation'];
    $hospital = $row['hospital'];
    $bloodtype = $row['bloodtype'];
    $wb_component = $row['wb_component'];
    $no_of_units = $row['no_of_units'];
} else {
    echo "No data found for this user!";
    exit;
}
// Free result set
$result->free();
$stmt->close();

// Process form submission to update data
if (isset($_POST['update'])) {
    $body_weight = $_POST['body_weight'];
    $sp_gr = $_POST['sp_gr'];
    $hgb = $_POST['hgb'];
    $rbc = $_POST['rbc'];
    $wbc = $_POST['wbc'];
    $plt_count = $_POST['plt_count'];
    $blood_type = $_POST['blood_type'];
    $type_of_donation = $_POST['type_of_donation'];
    $hospital = $_POST['hospital'];
    $bloodtype = $_POST['bloodtype'];
    $wb_component = $_POST['wb_component'];
    $no_of_units = $_POST['no_of_units'];

    // Update the initial_screening table
    $update_query = "UPDATE initial_screening SET body_weight = ?, sp_gr = ?, hgb = ?, rbc = ?, wbc = ?, plt_count = ?, blood_type = ?, type_of_donation = ?, hospital = ?, bloodtype = ?, wb_component = ?, no_of_units = ?, staff_id = ?, status = 'verified' WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);

    if (!$stmt) {
        die("Error preparing statement for initial_screening update: " . $conn->error);
    }

    $stmt->bind_param(
        "dddddsssssssss",
        $body_weight,
        $sp_gr,
        $hgb,
        $rbc,
        $wbc,
        $plt_count,
        $blood_type,
        $type_of_donation,
        $hospital,
        $bloodtype,
        $wb_component,
        $no_of_units,
        $staff_id,
        $user_id
    );

    if ($stmt->execute()) {
        $stmt->close();

        // Insert into physical_examination table
        $insert_query = "INSERT INTO physical_examination (user_id) VALUES (?)";
        $insert_stmt = $conn->prepare($insert_query);

        if (!$insert_stmt) {
            die("Error preparing statement for physical_examination insert: " . $conn->error);
        }

        $insert_stmt->bind_param("s", $user_id);

        if ($insert_stmt->execute()) {
            $insert_stmt->close();

            // Insert into blood_donation_history table
            $history_query = "INSERT INTO blood_donation_history (user_id, blood_group) VALUES (?, ?)";
            $history_stmt = $conn->prepare($history_query);

            if (!$history_stmt) {
                die("Error preparing statement for blood_donation_history insert: " . $conn->error);
            }

            $history_stmt->bind_param("ss", $user_id, $bloodtype);

            if ($history_stmt->execute()) {
                $history_stmt->close();
                header("Location: initial_patient.php");
                exit;
            } else {
                echo "Error inserting into blood_donation_history: " . $history_stmt->error;
            }
            $history_stmt->close();
        } else {
            echo "Error inserting into physical_examination: " . $insert_stmt->error;
        }
    } else {
        echo "Error updating initial_screening: " . $stmt->error;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interviewer: Update Initial Screening</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }

        h1 {
            color: #d71c1c;
            font-size: 24px;
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            margin-top: 10px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #d71c1c;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #b01616;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Interviewer: Update Initial Screening for User ID: <?php echo htmlspecialchars($user_id); ?></h1>

        <form method="POST" action="">
            <label for="body_weight">Body Weight</label>
            <input type="text" id="body_weight" name="body_weight" value="<?php echo htmlspecialchars($body_weight); ?>" required>

            <label for="sp_gr">Specific Gravity (sp_gr)</label>
            <input type="text" id="sp_gr" name="sp_gr" value="<?php echo htmlspecialchars($sp_gr); ?>" required>

            <label for="hgb">Hemoglobin (hgb)</label>
            <input type="text" id="hgb" name="hgb" value="<?php echo htmlspecialchars($hgb); ?>" required>

            <label for="rbc">RBC Count</label>
            <input type="text" id="rbc" name="rbc" value="<?php echo htmlspecialchars($rbc); ?>" required>

            <label for="wbc">WBC Count</label>
            <input type="text" id="wbc" name="wbc" value="<?php echo htmlspecialchars($wbc); ?>" required>

            <label for="plt_count">Platelet Count</label>
            <input type="text" id="plt_count" name="plt_count" value="<?php echo htmlspecialchars($plt_count); ?>" required>

            <label for="blood_type">Blood Type</label>
            <input type="text" id="blood_type" name="blood_type" value="<?php echo htmlspecialchars($blood_type); ?>" required>

            <label for="type_of_donation">Type of Donation</label>
            <select id="type_of_donation" name="type_of_donation" required>
                <option value="pending" <?php if ($type_of_donation == 'pending') echo 'selected'; ?>>Pending</option>
                <option value="walkin" <?php if ($type_of_donation == 'walkin') echo 'selected'; ?>>Walk-in</option>
                <option value="replacement" <?php if ($type_of_donation == 'replacement') echo 'selected'; ?>>Replacement</option>
                <option value="patient_directed" <?php if ($type_of_donation == 'patient_directed') echo 'selected'; ?>>Patient Directed</option>
                <option value="mobile blood donation" <?php if ($type_of_donation == 'mobile blood donation') echo 'selected'; ?>>Mobile Blood Donation</option>
            </select>

            <label for="hospital">Hospital</label>
            <input type="text" id="hospital" name="hospital" value="<?php echo htmlspecialchars($hospital); ?>" required>

            <label for="bloodtype">Blood Type (Detailed)</label>
            <input type="text" id="bloodtype" name="bloodtype" value="<?php echo htmlspecialchars($bloodtype); ?>" required>

            <label for="wb_component">WB Component</label>
            <input type="text" id="wb_component" name="wb_component" value="<?php echo htmlspecialchars($wb_component); ?>" required>

            <label for="no_of_units">Number of Units</label>
            <input type="text" id="no_of_units" name="no_of_units" value="<?php echo htmlspecialchars($no_of_units); ?>" required>

            <button type="submit" name="update">Update Screening</button>
        </form>
    </div>

</body>
</html>
