<?php
// Start the session
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

// Fetch staff details
$staff_firstname = '';
$staff_middlename = '';
$staff_surname = '';
$staff_name = 'Staff';

$staff_query = "SELECT firstname, middlename, surname FROM staff_account WHERE id = ?";
$staff_stmt = $conn->prepare($staff_query);

if ($staff_stmt === false) {
    // Handle prepare error - just continue without staff details
    error_log("Error preparing staff query: " . $conn->error);
} else {
    $staff_stmt->bind_param("i", $staff_id);
    if ($staff_stmt->execute()) {
        $staff_stmt->bind_result($staff_firstname, $staff_middlename, $staff_surname);
        $staff_stmt->fetch();
        $staff_name = trim("$staff_firstname $staff_middlename $staff_surname");
    } else {
        error_log("Error executing staff query: " . $staff_stmt->error);
    }
    $staff_stmt->close();
}

// Check if the user's blood group is "I don't know"
$check_query = "SELECT blood_group, firstname, surname FROM users WHERE id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $user_id);
$check_stmt->execute();
$check_stmt->bind_result($current_blood_group, $user_firstname, $user_lastname);
$check_stmt->fetch();
$check_stmt->close();

// Update blood_group if it is "I don't know"
if ($current_blood_group === "I don't know") {
    $update_query = "UPDATE users SET blood_group = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);

    if (!$update_stmt) {
        die("Error preparing statement for updating users table: " . $conn->error);
    }

    $update_stmt->bind_param("ss", $blood_type, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

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
    $bloodtype = isset($row['blood_component']) ? $row['blood_component'] : ''; // Handle undefined index
    $no_of_units = $row['no_of_units'];
    
    // Initialize screening question answers
    $q1_value = isset($row['Q1']) ? $row['Q1'] : '';
    $q2_value = isset($row['Q2']) ? $row['Q2'] : '';
    $q3_value = isset($row['Q3']) ? $row['Q3'] : '';
    $q4_value = isset($row['Q4']) ? $row['Q4'] : '';
    $q5_value = isset($row['Q5']) ? $row['Q5'] : '';
    $q6_value = isset($row['Q6']) ? $row['Q6'] : '';
    $q7_value = isset($row['Q7']) ? $row['Q7'] : '';
    $q8_value = isset($row['Q8']) ? $row['Q8'] : '';
    $q9_value = isset($row['Q9']) ? $row['Q9'] : '';
    $q10_value = isset($row['Q10']) ? $row['Q10'] : '';
} else {
    // Initialize with default values if no data found
    $body_weight = '';
    $sp_gr = '';
    $hgb = '';
    $rbc = '';
    $wbc = '';
    $plt_count = '';
    $blood_type = '';
    $type_of_donation = 'pending';
    $hospital = '';
    $bloodtype = '';
    $no_of_units = '';
    
    // Initialize screening question answers with empty values
    $q1_value = '';
    $q2_value = '';
    $q3_value = '';
    $q4_value = '';
    $q5_value = '';
    $q6_value = '';
    $q7_value = '';
    $q8_value = '';
    $q9_value = '';
    $q10_value = '';
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
    $bloodtype = $_POST['blood_component']; // Ensure this matches the form input name
    $no_of_units = $_POST['no_of_units'];
    
    // Get screening question answers
    $q1 = isset($_POST['Q1']) ? $_POST['Q1'] : null;
    $q2 = isset($_POST['Q2']) ? $_POST['Q2'] : null;
    $q3 = isset($_POST['Q3']) ? $_POST['Q3'] : null;
    $q4 = isset($_POST['Q4']) ? $_POST['Q4'] : null;
    $q5 = isset($_POST['Q5']) ? $_POST['Q5'] : null;
    $q6 = isset($_POST['Q6']) ? $_POST['Q6'] : null;
    $q7 = isset($_POST['Q7']) ? $_POST['Q7'] : null;
    $q8 = isset($_POST['Q8']) ? $_POST['Q8'] : null;
    $q9 = isset($_POST['Q9']) ? $_POST['Q9'] : null;
    $q10 = isset($_POST['Q10']) ? $_POST['Q10'] : null;

    // Update blood_group in users table based on submitted blood_type
    $update_blood_group_query = "UPDATE users SET blood_group = ? WHERE id = ?";
    $update_blood_group_stmt = $conn->prepare($update_blood_group_query);
    
    if (!$update_blood_group_stmt) {
        die("Error preparing statement for updating blood group: " . $conn->error);
    }

    $update_blood_group_stmt->bind_param("ss", $blood_type, $user_id);
    if (!$update_blood_group_stmt->execute()) {
        die("Error updating blood group: " . $update_blood_group_stmt->error);
    }
    $update_blood_group_stmt->close();

    // Delete schedule data for the user
    $delete_schedule_query = "DELETE FROM schedule WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_schedule_query);
    
    if (!$delete_stmt) {
        die("Error preparing statement for schedule deletion: " . $conn->error);
    }

    $delete_stmt->bind_param("s", $user_id);
    if (!$delete_stmt->execute()) {
        die("Error deleting schedule: " . $delete_stmt->error);
    }
    $delete_stmt->close();

    try {
        // Make sure values are properly formatted for SQL
        $body_weight = floatval($body_weight);
        $sp_gr = floatval($sp_gr);
        $hgb = floatval($hgb);
        $rbc = floatval($rbc);
        $wbc = floatval($wbc);
        $plt_count = intval($plt_count);
        $no_of_units = intval($no_of_units);
        $staff_id = intval($staff_id);
        $user_id = intval($user_id);

        // Escape string values
        $blood_type = $conn->real_escape_string($blood_type);
        $type_of_donation = $conn->real_escape_string($type_of_donation);
        $hospital = $conn->real_escape_string($hospital);
        $bloodtype = $conn->real_escape_string($bloodtype);
        $q1 = $conn->real_escape_string($q1 ?? '');
        $q2 = $conn->real_escape_string($q2 ?? '');
        $q3 = $conn->real_escape_string($q3 ?? '');
        $q4 = $conn->real_escape_string($q4 ?? '');
        $q5 = $conn->real_escape_string($q5 ?? '');
        $q6 = $conn->real_escape_string($q6 ?? '');
        $q7 = $conn->real_escape_string($q7 ?? '');
        $q8 = $conn->real_escape_string($q8 ?? '');
        $q9 = $conn->real_escape_string($q9 ?? '');
        $q10 = $conn->real_escape_string($q10 ?? '');

        // Check if record exists
        $check_exists = "SELECT 1 FROM initial_screening WHERE user_id = $user_id LIMIT 1";
        $result = $conn->query($check_exists);
        $exists = $result && $result->num_rows > 0;
        
        if ($exists) {
            // UPDATE query
            $query = "UPDATE initial_screening 
                      SET body_weight = $body_weight, 
                          sp_gr = $sp_gr, 
                          hgb = $hgb, 
                          rbc = $rbc, 
                          wbc = $wbc, 
                          plt_count = $plt_count, 
                          blood_type = '$blood_type', 
                          type_of_donation = '$type_of_donation', 
                          hospital = '$hospital', 
                          blood_component = '$bloodtype', 
                          no_of_units = $no_of_units, 
                          staff_id = $staff_id, 
                          status = 'verified',
                          Q1 = " . (!empty($q1) ? "'$q1'" : "NULL") . ", 
                          Q2 = " . (!empty($q2) ? "'$q2'" : "NULL") . ", 
                          Q3 = " . (!empty($q3) ? "'$q3'" : "NULL") . ", 
                          Q4 = " . (!empty($q4) ? "'$q4'" : "NULL") . ", 
                          Q5 = " . (!empty($q5) ? "'$q5'" : "NULL") . ", 
                          Q6 = " . (!empty($q6) ? "'$q6'" : "NULL") . ", 
                          Q7 = " . (!empty($q7) ? "'$q7'" : "NULL") . ", 
                          Q8 = " . (!empty($q8) ? "'$q8'" : "NULL") . ", 
                          Q9 = " . (!empty($q9) ? "'$q9'" : "NULL") . ", 
                          Q10 = " . (!empty($q10) ? "'$q10'" : "NULL") . " 
                      WHERE user_id = $user_id";
        } else {
            // INSERT query
            $query = "INSERT INTO initial_screening 
                      (user_id, body_weight, sp_gr, hgb, rbc, wbc, plt_count, blood_type, 
                       type_of_donation, hospital, blood_component, no_of_units, staff_id, status,
                       Q1, Q2, Q3, Q4, Q5, Q6, Q7, Q8, Q9, Q10) 
                      VALUES 
                      ($user_id, $body_weight, $sp_gr, $hgb, $rbc, $wbc, $plt_count, '$blood_type', 
                       '$type_of_donation', '$hospital', '$bloodtype', $no_of_units, $staff_id, 'verified',
                       " . (!empty($q1) ? "'$q1'" : "NULL") . ", 
                       " . (!empty($q2) ? "'$q2'" : "NULL") . ", 
                       " . (!empty($q3) ? "'$q3'" : "NULL") . ", 
                       " . (!empty($q4) ? "'$q4'" : "NULL") . ", 
                       " . (!empty($q5) ? "'$q5'" : "NULL") . ", 
                       " . (!empty($q6) ? "'$q6'" : "NULL") . ", 
                       " . (!empty($q7) ? "'$q7'" : "NULL") . ", 
                       " . (!empty($q8) ? "'$q8'" : "NULL") . ", 
                       " . (!empty($q9) ? "'$q9'" : "NULL") . ", 
                       " . (!empty($q10) ? "'$q10'" : "NULL") . ")";
        }

        error_log("SQL: $query");
        
        if (!$conn->query($query)) {
            throw new Exception("Error updating initial_screening: " . $conn->error);
        }

        // Continue with the rest of your code
        // Insert into physical_examination table
        $insert_query = "INSERT INTO physical_examination (user_id) VALUES (?)";
        $insert_stmt = $conn->prepare($insert_query);

        if (!$insert_stmt) {
            throw new Exception("Error preparing statement for physical_examination insert: " . $conn->error);
        }

        $insert_stmt->bind_param("s", $user_id);

        if ($insert_stmt->execute()) {
            $insert_stmt->close();

            $history_query = "INSERT INTO blood_donation_history (user_id, blood_type, location) VALUES (?, ?, ?)";
            $history_stmt = $conn->prepare($history_query);

            if (!$history_stmt) {
                throw new Exception("Error preparing statement for blood_donation_history insert: " . $conn->error);
            }

            $history_stmt->bind_param("sss", $user_id, $blood_type, $hospital);

            if ($history_stmt->execute()) {
                $history_stmt->close();
                header("Location: initial_patient.php");
                exit;
            } else {
                throw new Exception("Error inserting into blood_donation_history: " . $history_stmt->error);
            }
            $history_stmt->close();
        } else {
            throw new Exception("Error inserting into physical_examination: " . $insert_stmt->error);
        }
    } catch (Exception $e) {
        echo '<div class="message error">';
        echo '<p>Error: ' . $e->getMessage() . '</p>';
        echo '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Screening Dashboard</title>
    <style>
        :root {
            --primary: #d32f2f;
            --primary-dark: #b71c1c;
            --secondary: #2196f3;
            --secondary-dark: #1976d2;
            --text-dark: #333;
            --text-light: #f5f5f5;
            --bg-light: #f9f9f9;
            --bg-dark: #e0e0e0;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        .header {
            background-color: var(--primary);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: bold;
        }

        .main-content {
            display: flex;
            padding: 1.5rem;
            gap: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            width: 100%;
        }

        .card-header {
            padding: 1rem;
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.2rem;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .donor-info {
            flex: 1;
            max-width: 400px;
        }

        .screening-form {
            flex: 2;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .info-item {
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .info-item strong {
            display: block;
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .info-item span {
            font-size: 1rem;
            font-weight: 500;
        }

        .blood-type {
            grid-column: span 2;
            text-align: center;
            padding: 1rem;
            margin: 1rem 0;
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .blood-type span {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        input, select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
        }

        button {
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            align-self: flex-end;
        }

        button:hover {
            background-color: var(--primary-dark);
        }

        .info-icon {
            cursor: help;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #666;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .warning {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
            border: 1px solid rgba(255, 152, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .main-content {
                flex-direction: column;
            }
            
            .donor-info {
                max-width: 100%;
            }
            
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Blood Bank Management System</h1>
        <div class="header-right">
            <div class="user-info">
                <div class="user-avatar"><?php echo substr($staff_firstname, 0, 1); ?></div>
                <span><?php echo htmlspecialchars($staff_name); ?></span>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h2>Initial Screening</h2>
                <div>Donor ID: <?php echo $user_id; ?></div>
            </div>
            <div class="card-body">
                <?php if ($current_blood_group == "I don't know"): ?>
                <div class="message warning">
                    <p>Donor's blood group is unknown. Please update after screening.</p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="body_weight">Body Weight (kg)</label>
                            <input type="text" id="body_weight" name="body_weight" value="<?php echo htmlspecialchars($body_weight); ?>" required placeholder="e.g., 70.5">
                        </div>
                        <div class="form-group">
                            <label for="sp_gr">Specific Gravity</label>
                            <input type="text" id="sp_gr" name="sp_gr" value="<?php echo htmlspecialchars($sp_gr); ?>" required placeholder="e.g., 1.025">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="hgb">Hemoglobin (g/dL)</label>
                            <input type="text" id="hgb" name="hgb" value="<?php echo htmlspecialchars($hgb); ?>" required placeholder="e.g., 14.5">
                        </div>
                        <div class="form-group">
                            <label for="rbc">RBC Count (x10^6/μL)</label>
                            <input type="text" id="rbc" name="rbc" value="<?php echo htmlspecialchars($rbc); ?>" required placeholder="e.g., 5.2">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="wbc">WBC Count (x10^3/μL)</label>
                            <input type="text" id="wbc" name="wbc" value="<?php echo htmlspecialchars($wbc); ?>" required placeholder="e.g., 7.5">
                        </div>
                        <div class="form-group">
                            <label for="plt_count">Platelet Count (x10^3/μL)</label>
                            <input type="text" id="plt_count" name="plt_count" value="<?php echo htmlspecialchars($plt_count); ?>" required placeholder="e.g., 250">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="blood_type">Blood Type</label>
                            <select id="blood_type" name="blood_type" required>
                                <option value="">--Select--</option>
                                <option value="A+" <?php if ($blood_type == 'A+') echo 'selected'; ?>>A+</option>
                                <option value="A-" <?php if ($blood_type == 'A-') echo 'selected'; ?>>A-</option>
                                <option value="B+" <?php if ($blood_type == 'B+') echo 'selected'; ?>>B+</option>
                                <option value="B-" <?php if ($blood_type == 'B-') echo 'selected'; ?>>B-</option>
                                <option value="AB+" <?php if ($blood_type == 'AB+') echo 'selected'; ?>>AB+</option>
                                <option value="AB-" <?php if ($blood_type == 'AB-') echo 'selected'; ?>>AB-</option>
                                <option value="O+" <?php if ($blood_type == 'O+') echo 'selected'; ?>>O+</option>
                                <option value="O-" <?php if ($blood_type == 'O-') echo 'selected'; ?>>O-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type_of_donation">Type of Donation</label>
                            <select id="type_of_donation" name="type_of_donation" required>
                                <option value="pending" <?php if ($type_of_donation == 'pending') echo 'selected'; ?>>Pending</option>
                                <option value="walkin" <?php if ($type_of_donation == 'walkin') echo 'selected'; ?>>Walk-in</option>
                                <option value="replacement" <?php if ($type_of_donation == 'replacement') echo 'selected'; ?>>Replacement</option>
                                <option value="patient_directed" <?php if ($type_of_donation == 'patient_directed') echo 'selected'; ?>>Patient Directed</option>
                                <option value="mobile blood donation" <?php if ($type_of_donation == 'mobile blood donation') echo 'selected'; ?>>Mobile Blood Donation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="hospital">Hospital/Location</label>
                            <input type="text" id="hospital" name="hospital" value="<?php echo htmlspecialchars($hospital); ?>" required placeholder="e.g., City General Hospital">
                        </div>
                        <div class="form-group">
                            <label for="no_of_units">Number of Units</label>
                            <input type="text" id="no_of_units" name="no_of_units" value="<?php echo htmlspecialchars($no_of_units); ?>" required placeholder="e.g., 1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="blood_component">Blood Component</label>
                        <select id="blood_component" name="blood_component" required>
                            <option value="None" <?php if ($bloodtype == "None") echo "selected"; ?>>None</option>
                            <option value="whole_blood" <?php if ($bloodtype == "whole_blood") echo "selected"; ?>>Whole Blood</option>
                            <option value="packed_rbc" <?php if ($bloodtype == "packed_rbc") echo "selected"; ?>>Packed RBC</option>
                            <option value="washed_rbc" <?php if ($bloodtype == "washed_rbc") echo "selected"; ?>>Washed RBC</option>
                            <option value="buffy_coat_poor_rbc" <?php if ($bloodtype == "buffy_coat_poor_rbc") echo "selected"; ?>>Buffy Coat Poor RBC</option>
                            <option value="platelet_concentrate" <?php if ($bloodtype == "platelet_concentrate") echo "selected"; ?>>Platelet Concentrate</option>
                            <option value="apheresis_platelet" <?php if ($bloodtype == "apheresis_platelet") echo "selected"; ?>>Apheresis Platelet</option>
                            <option value="leukocyte_poor_platelet_concentrate" <?php if ($bloodtype == "leukocyte_poor_platelet_concentrate") echo "selected"; ?>>Leukocyte Poor Platelet Concentrate</option>
                            <option value="fresh_frozen_plasma" <?php if ($bloodtype == "fresh_frozen_plasma") echo "selected"; ?>>Fresh Frozen Plasma</option>
                            <option value="leukocyte_poor_fresh_frozen_plasma" <?php if ($bloodtype == "leukocyte_poor_fresh_frozen_plasma") echo "selected"; ?>>Leukocyte Poor Fresh Frozen Plasma</option>
                            <option value="cryoprecipitate" <?php if ($bloodtype == "cryoprecipitate") echo "selected"; ?>>Cryoprecipitate</option>
                        </select>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Screening Questions</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="Q1">Do you feel well and healthy today?</label>
                                <select id="Q1" name="Q1" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q1_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q1_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q2">Taken alcohol in the last 12 hours?</label>
                                <select id="Q2" name="Q2" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q2_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q2_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q3">Taken aspirin in the last 3 days?</label>
                                <select id="Q3" name="Q3" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q3_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q3_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q4">Medications or vaccines in the last 8 weeks?</label>
                                <select id="Q4" name="Q4" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q4_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q4_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q5">Donated blood in the past 3 months?</label>
                                <select id="Q5" name="Q5" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q5_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q5_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q6">Visited Zika-infected areas in the past 6 months?</label>
                                <select id="Q6" name="Q6" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q6_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q6_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q7">Had sexual contact with a Zika-infected person?</label>
                                <select id="Q7" name="Q7" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q7_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q7_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q8">Received blood or had surgery in the last 12 months?</label>
                                <select id="Q8" name="Q8" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q8_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q8_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q9">Tattoo, piercing, or contact with blood in the last 12 months?</label>
                                <select id="Q9" name="Q9" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q9_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q9_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="Q10">High-risk sexual contact in the last 12 months?</label>
                                <select id="Q10" name="Q10" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Yes" <?php if ($q10_value == 'Yes') echo 'selected'; ?>>Yes</option>
                                    <option value="No" <?php if ($q10_value == 'No') echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update">Update Screening</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Set the blood type dropdown to match the current value
        document.addEventListener('DOMContentLoaded', function() {
            const bloodTypeSelect = document.getElementById('blood_type');
            const currentBloodType = '<?php echo $blood_type; ?>';
            
            if (currentBloodType) {
                for (let i = 0; i < bloodTypeSelect.options.length; i++) {
                    if (bloodTypeSelect.options[i].value === currentBloodType) {
                        bloodTypeSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    </script>
</body>
</html>

