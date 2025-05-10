<?php
// Start the session (optional, if you are using sessions)
session_start();

// Assuming a connection is established to your database
include('connection.php');

// Retrieve the user_id from the URL query string
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// Get the staff_id from session
$staff_id = isset($_SESSION['staff_id']) ? $_SESSION['staff_id'] : null;

// Fetch the initial screening data
$fetch_screening_query = "SELECT user_id, body_weight, sp_gr, hgb, rbc, wbc, plt_count, blood_type, 
                          type_of_donation, staff_id, hospital, blood_component, no_of_units,
                          Q1, Q2, Q3, Q4, Q5, Q6, Q7, Q8, Q9, Q10 
                          FROM initial_screening WHERE user_id = ?";
if ($stmt_screening = $conn->prepare($fetch_screening_query)) {
    $stmt_screening->bind_param("i", $user_id);
    $stmt_screening->execute();
    $result = $stmt_screening->get_result();
    $screening_data = $result->fetch_assoc();
    $stmt_screening->close();
}

// Create a map for question descriptions
$question_descriptions = [
    'Q1' => 'Do you feel well and healthy today?',
    'Q2' => 'Taken alcohol in the last 12 hours?',
    'Q3' => 'Taken aspirin in the last 3 days?',
    'Q4' => 'Medications or vaccines in the last 8 weeks?',
    'Q5' => 'Donated blood in the past 3 months?',
    'Q6' => 'Visited Zika-infected areas in the past 6 months?',
    'Q7' => 'Had sexual contact with a Zika-infected person?',
    'Q8' => 'Received blood or had surgery in the last 12 months?',
    'Q9' => 'Tattoo, piercing, or contact with blood in the last 12 months?',
    'Q10' => 'High-risk sexual contact in the last 12 months?'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_pressure = $_POST['blood_pressure'];
    $pulse_rate = $_POST['pulse_rate'];
    $body_temp = $_POST['body_temp'];

    // General Appearance
    $gen_appearance = ($_POST['gen_appearance'] == "Others") ? $_POST['gen_appearance_other'] : $_POST['gen_appearance'];

    // Skin
    $skin = ($_POST['skin'] == "Others") ? $_POST['skin_other'] : $_POST['skin'];

    // HEENT
    $heent = ($_POST['heent'] == "Others") ? $_POST['heent_other'] : $_POST['heent'];

    // Heart and Lungs
    $heart_lungs = ($_POST['heart_lungs'] == "Others") ? $_POST['heart_lungs_other'] : $_POST['heart_lungs'];

    $remarks = $_POST['remarks'];
    $reason = $_POST['reason'];
    $bags_used = $_POST['bags_used'];

    // Update the physical_examination table
    $update_query = "UPDATE physical_examination SET
        blood_pressure = ?, pulse_rate = ?, body_temp = ?, gen_appearance = ?, skin = ?,
        heent = ?, heart_lungs = ?, remarks = ?, reason = ?, bags_used = ?, staff_id = ?, status = 'Verified'
        WHERE user_id = ?";

    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("sssssssssssi", $blood_pressure, $pulse_rate, $body_temp, $gen_appearance, $skin,
            $heent, $heart_lungs, $remarks, $reason, $bags_used, $staff_id, $user_id);

        if ($stmt->execute()) {
            // Insert user_id into the blood_collection table
            $insert_blood_collection_query = "INSERT INTO blood_collection (user_id) VALUES (?)";
            if ($stmt_blood_collection = $conn->prepare($insert_blood_collection_query)) {
                $stmt_blood_collection->bind_param("i", $user_id);
                $stmt_blood_collection->execute();
                $stmt_blood_collection->close();
            }

            // Redirect after successful update
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
    <title>Blood Bank Management System</title>
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

        .screening-info {
            flex: 1;
            max-width: 400px;
        }

        .physical-exam {
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

        input, select, textarea {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
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

        /* Styles for screening questions */
        .section-title {
            margin: 1.5rem 0 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .screening-questions .info-grid {
            grid-template-columns: 1fr;
        }

        .screening-question {
            padding: 0.75rem;
            border-bottom: 1px solid #f5f5f5;
        }

        .screening-question strong {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .text-danger {
            color: var(--danger);
            font-weight: bold;
        }

        .text-success {
            color: var(--success);
            font-weight: bold;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .main-content {
                flex-direction: column;
            }
            
            .screening-info {
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
                <div class="user-avatar">S</div>
                <span>Staff</span>
            </div>
        </div>
    </header>

    <div class="main-content">
        <!-- Initial Screening Information Card -->
        <div class="card screening-info">
            <div class="card-header">
                <h2>Initial Screening Information</h2>
                <div>ID: <?php echo $user_id; ?></div>
            </div>
            <div class="card-body">
                <?php if (isset($screening_data) && $screening_data): ?>
                    <div class="blood-type">
                        <strong>Blood Type</strong>
                        <span><?php echo $screening_data['blood_type']; ?></span>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Body Weight</strong>
                            <span><?php echo $screening_data['body_weight']; ?> kg</span>
                        </div>
                        <div class="info-item">
                            <strong>Specific Gravity</strong>
                            <span><?php echo $screening_data['sp_gr']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Hemoglobin</strong>
                            <span><?php echo $screening_data['hgb']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>RBC</strong>
                            <span><?php echo $screening_data['rbc']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>WBC</strong>
                            <span><?php echo $screening_data['wbc']; ?> </span>
                        </div>
                        <div class="info-item">
                            <strong>Platelet Count</strong>
                            <span><?php echo $screening_data['plt_count']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Type of Donation</strong>
                            <span><?php echo $screening_data['type_of_donation']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Hospital</strong>
                            <span><?php echo $screening_data['hospital']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Blood Component</strong>
                            <span><?php echo $screening_data['blood_component']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Number of Units</strong>
                            <span><?php echo $screening_data['no_of_units']; ?></span>
                        </div>
                    </div>

                    <!-- Add Screening Questions Section -->
                    <div class="screening-questions">
                        <h3 class="section-title">Screening Questions</h3>
                        <div class="info-grid">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <?php 
                                $question_key = "Q$i";
                                $answer = isset($screening_data[$question_key]) ? $screening_data[$question_key] : 'Not answered';
                                ?>
                                <div class="info-item screening-question">
                                    <strong><?php echo htmlspecialchars($question_descriptions[$question_key]); ?></strong>
                                    <span class="<?php echo ($answer == 'Yes') ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo htmlspecialchars($answer); ?>
                                    </span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p>No screening data available for this user.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Physical Examination Form Card -->
        <div class="card physical-exam">
            <div class="card-header">
                <h2>Update Physical Examination</h2>
            </div>
            <div class="card-body">
                <!-- Display success or error message -->
                <?php if (isset($message)): ?>
                    <div class="message success"><?php echo $message; ?></div>
                <?php elseif (isset($error_message)): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="blood_pressure">Blood Pressure</label>
                            <input type="text" id="blood_pressure" name="blood_pressure" required placeholder="e.g., 120/80 mmHg">
                        </div>
                        <div class="form-group">
                            <label for="pulse_rate">Pulse Rate</label>
                            <input type="text" id="pulse_rate" name="pulse_rate" required placeholder="e.g., 72 bpm">
                        </div>
                        <div class="form-group">
                            <label for="body_temp">Body Temperature</label>
                            <input type="text" id="body_temp" name="body_temp" required placeholder="e.g., 36.8 °C">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gen_appearance">
                                General Appearance
                                <span class="info-icon" title="This section evaluates the overall physical state of the patient, checking for signs of weakness, illness, or abnormalities.">i</span>
                            </label>
                            <select id="gen_appearance" name="gen_appearance" required onchange="toggleInput(this, 'gen_appearance_other')">
                                <option value="Normal">Normal</option>
                                <option value="Pale">Pale</option>
                                <option value="Weak">Weak</option>
                                <option value="Ill-looking">Ill-looking</option>
                                <option value="Others">Others</option>
                            </select>
                            <input type="text" id="gen_appearance_other" name="gen_appearance_other" placeholder="Specify..." style="display: none;">
                        </div>
                        <div class="form-group">
                            <label for="skin">
                                Skin
                                <span class="info-icon" title="This section assesses the condition of the skin, checking for abnormalities such as rashes, jaundice, or paleness.">i</span>
                            </label>
                            <select id="skin" name="skin" required onchange="toggleInput(this, 'skin_other')">
                                <option value="Normal">Normal</option>
                                <option value="Pale">Pale</option>
                                <option value="Jaundiced">Jaundiced</option>
                                <option value="Rashes">Rashes</option>
                                <option value="Others">Others</option>
                            </select>
                            <input type="text" id="skin_other" name="skin_other" placeholder="Specify..." style="display: none;">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="heent">
                                HEENT
                                <span class="info-icon" title="HEENT stands for Head, Eyes, Ears, Nose, and Throat. This section assesses the condition of these areas for any abnormalities.">i</span>
                            </label>
                            <select id="heent" name="heent" required onchange="toggleInput(this, 'heent_other')">
                                <option value="Normal">Normal</option>
                                <option value="Pale Conjunctiva">Pale Conjunctiva</option>
                                <option value="Icteric Sclera">Icteric Sclera</option>
                                <option value="Enlarged Tonsils">Enlarged Tonsils</option>
                                <option value="Others">Others</option>
                            </select>
                            <input type="text" id="heent_other" name="heent_other" placeholder="Specify..." style="display: none;">
                        </div>
                        <div class="form-group">
                            <label for="heart_lungs">
                                Heart and Lungs
                                <span class="info-icon" title="This section assesses heart and lung health, identifying irregular heartbeats, murmurs, wheezing, or other abnormalities.">i</span>
                            </label>
                            <select id="heart_lungs" name="heart_lungs" required onchange="toggleInput(this, 'heart_lungs_other')">
                                <option value="Normal">Normal</option>
                                <option value="Irregular Heartbeat">Irregular Heartbeat</option>
                                <option value="Murmur">Murmur</option>
                                <option value="Wheezes">Wheezes</option>
                                <option value="Crackles">Crackles</option>
                                <option value="Others">Others</option>
                            </select>
                            <input type="text" id="heart_lungs_other" name="heart_lungs_other" placeholder="Specify..." style="display: none;">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <select id="remarks" name="remarks" required>
                                <option value="Accepted">Accepted</option>
                                <option value="Temporary Deferred">Temporary Deferred</option>
                                <option value="Permanently Deferred">Permanently Deferred</option>
                                <option value="Refused">Refused</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bags_used">Bags Used</label>
                            <select id="bags_used" name="bags_used" required>
                                <option value="single">Single</option>
                                <option value="multiple">Multiple</option>
                                <option value="top&bottom apheresis">Top & Bottom Apheresis</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="reason-container" style="display: none;">
                        <label for="reason">Reason for Deferral/Refusal</label>
                        <textarea id="reason" name="reason" rows="4" placeholder="Enter detailed reason..."></textarea>
                    </div>

                    <button type="submit">Update Physical Examination</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to toggle the "Other" input fields
        function toggleInput(selectElement, inputId) {
            var inputElement = document.getElementById(inputId);
            if (selectElement.value === "Others") {
                inputElement.style.display = "block";
                inputElement.required = true;
            } else {
                inputElement.style.display = "none";
                inputElement.required = false;
            }
        }

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

