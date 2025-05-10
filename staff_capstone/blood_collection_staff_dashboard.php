<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Include database connection
include('connection.php');

if (!isset($_SESSION['staff_id'])) {
    die("<p>Error: Staff ID not found in session.</p>");
}
$staff_id = $_SESSION['staff_id'];

if (!isset($_GET['user_id'])) {
    die("<p>Error: User ID not provided.</p>");
}
$user_id = $_GET['user_id'];

// Fetch staff details
$query = "SELECT firstname, middlename, surname FROM staff_account WHERE id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $stmt->bind_result($staff_firstname, $staff_middlename, $staff_surname);
    $stmt->fetch();
    $stmt->close();
}
$staff_name = trim("$staff_firstname $staff_middlename $staff_surname");

// Fetch existing blood collection record
$query = "SELECT * FROM blood_collection WHERE user_id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        extract($row);
    } else {
        die("<p>No record found for this user.</p>");
    }
    $stmt->close();
}

// Fetch data from physical_examination table
$exam_query = "SELECT user_id, blood_pressure, pulse_rate, body_temp, gen_appearance, skin, heent, heart_lungs, remarks, reason, bags_used 
               FROM physical_examination WHERE user_id = ?";
if ($stmt = $conn->prepare($exam_query)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $exam_result = $stmt->get_result();
    
    if ($exam_result->num_rows > 0) {
        $exam_data = $exam_result->fetch_assoc();
    } else {
        // Handle the case where no record is found
        echo "<p>No physical examination record found for this user.</p>";
        exit; // Stop further execution
    }
    $stmt->close();
} else {
    // Handle any error with preparing the statement
    echo "<p>Error preparing query: " . $conn->error . "</p>";
    exit;
}

// Now check if $exam_data is not null before accessing the fields
if (isset($exam_data)) {
    $blood_pressure = $exam_data['blood_pressure'] ?? 'Not available';
    $pulse_rate = $exam_data['pulse_rate'] ?? 'Not available';
    $body_temp = $exam_data['body_temp'] ?? 'Not available';
    $gen_appearance = $exam_data['gen_appearance'] ?? 'Not available';
    $skin = $exam_data['skin'] ?? 'Not available';
    $heent = $exam_data['heent'] ?? 'Not available';
    $heart_lungs = $exam_data['heart_lungs'] ?? 'Not available';
    $remarks = $exam_data['remarks'] ?? 'Not available';
    $reason = $exam_data['reason'] ?? 'Not available';
    $bags_used = $exam_data['bags_used'] ?? 'Not available';
} else {
    // Handle the case where $exam_data is null
    echo "<p>Data not available for this user.</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $blood_type = $_POST['blood_type'];
    $number_of_bags = $_POST['number_of_bags'];

    if (empty($blood_type)) {
        die("<p>Error: Blood type is missing from form submission.</p>");
    }

    $start_timestamp = strtotime($start_time);
    $end_timestamp = strtotime($end_time);
    $elapsed_time = gmdate("H:i:s", $end_timestamp - $start_timestamp);

    $status = 'Verified';

    // Update blood_collection
    $update_query = "UPDATE blood_collection 
                     SET karmi=?, terumo=?, special_bag=?, apheresis=?, amount_blood_taken=?, successful=?, donors_reaction=?, management_done=?, start_time=?, end_time=?, elapsed_time=?, status=?, staff_id=?, blood_group=?, number_of_bags=? 
                     WHERE user_id=?";
    
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("ssssssssssssssii", $karmi, $terumo, $special_bag, $apheresis, $amount_blood_taken, $successful, $donors_reaction, $management_done, $start_time, $end_time, $elapsed_time, $status, $staff_id, $blood_type, $number_of_bags, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        die("<p>Error updating blood_collection: " . $conn->error . "</p>");
    }

    // Update blood_donation_history
    $history_query = "UPDATE blood_donation_history SET donation_quantity=?, blood_group=? WHERE user_id=?";
    if ($stmt = $conn->prepare($history_query)) {
        $stmt->bind_param("dsi", $amount_blood_taken, $blood_type, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        die("<p>Error updating blood_donation_history: " . $conn->error . "</p>");
    }

    // Instead of fetching blood_group from blood_donation_history, fetch it from the users table.
    $blood_group_query = "SELECT blood_group FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($blood_group_query)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($fetched_blood_group);
        $stmt->fetch();
        $stmt->close();
    } else {
        die("<p>Error fetching blood group from users: " . $conn->error . "</p>");
    }
    
    // Verify that we have a blood group from the users table.
    if (empty($fetched_blood_group)) {
        die("<p>Error: Blood group not found in users for user ID $user_id.</p>");
    }

    // Insert into blood_collection_inventory
    $collection_date = date("Y-m-d H:i:s");
    $expiration_date = date("Y-m-d H:i:s", strtotime("+42 days"));
    $volume_ml = $amount_blood_taken; // If already in mL
    $status_inventory = "Available";

    $insert_query = "INSERT INTO blood_collection_inventory (user_id, blood_group, blood_type, collection_date, expiration_date, volume_ml, status, collected_by, number_of_bags, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    if ($stmt = $conn->prepare($insert_query)) {
        $stmt->bind_param("issssissi", $user_id, $blood_type, $fetched_blood_group, $collection_date, $expiration_date, $volume_ml, $status_inventory, $staff_name, $number_of_bags);
        
        if (!$stmt->execute()) {
            die("<p>Error executing blood_collection_inventory insert: " . $stmt->error . "</p>");
        }
        $stmt->close();
    } else {
        die("<p>Error preparing blood_collection_inventory insert: " . $conn->error . "</p>");
    }

    header("Location: blood_collection_patient.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Collection Dashboard</title>
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

        .exam-info {
            flex: 1;
            max-width: 400px;
        }

        .collection-form {
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

        .remarks-highlight {
            grid-column: span 2;
            text-align: center;
            padding: 1rem;
            margin: 1rem 0;
            background-color: #f5f5f5;
            border-radius: 4px;
            border-left: 4px solid var(--primary);
        }

        .remarks-highlight.accepted {
            border-left-color: var(--success);
        }

        .remarks-highlight.deferred {
            border-left-color: var(--warning);
        }

        .remarks-highlight.refused {
            border-left-color: var(--danger);
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
        }

        button:hover {
            background-color: var(--primary-dark);
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .timer-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .timer-display {
            font-size: 1.5rem;
            font-weight: bold;
            font-family: monospace;
            padding: 0.5rem 1rem;
            background-color: #333;
            color: white;
            border-radius: 4px;
            min-width: 120px;
            text-align: center;
        }

        .timer-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .timer-buttons button {
            padding: 0.5rem 1rem;
        }

        .submit-btn {
            align-self: flex-end;
            margin-top: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .main-content {
                flex-direction: column;
            }
            
            .exam-info {
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
        <!-- Physical Examination Info Card -->
        <div class="card exam-info">
            <div class="card-header">
                <h2>Physical Examination</h2>
                <div>ID: <?php echo $user_id; ?></div>
            </div>
            <div class="card-body">
                <?php if (isset($exam_data) && $exam_data): ?>
                    <div class="remarks-highlight <?php 
                        if ($exam_data['remarks'] == 'Accepted') echo 'accepted';
                        elseif (strpos($exam_data['remarks'], 'Deferred') !== false) echo 'deferred';
                        elseif ($exam_data['remarks'] == 'Refused') echo 'refused';
                    ?>">
                        <strong>Remarks</strong>
                        <div><?php echo htmlspecialchars($exam_data['remarks']); ?></div>
                        <?php if (!empty($exam_data['reason']) && $exam_data['reason'] != 'Not available'): ?>
                            <div class="reason"><?php echo htmlspecialchars($exam_data['reason']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Blood Pressure</strong>
                            <span><?php echo htmlspecialchars($exam_data['blood_pressure']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Pulse Rate</strong>
                            <span><?php echo htmlspecialchars($exam_data['pulse_rate']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Body Temperature</strong>
                            <span><?php echo htmlspecialchars($exam_data['body_temp']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>General Appearance</strong>
                            <span><?php echo htmlspecialchars($exam_data['gen_appearance']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Skin</strong>
                            <span><?php echo htmlspecialchars($exam_data['skin']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>HEENT</strong>
                            <span><?php echo htmlspecialchars($exam_data['heent']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Heart and Lungs</strong>
                            <span><?php echo htmlspecialchars($exam_data['heart_lungs']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Bags Used</strong>
                            <span><?php echo htmlspecialchars($exam_data['bags_used']); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p>No examination data available for this user.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Blood Collection Form Card -->
        <div class="card collection-form">
            <div class="card-header">
                <h2>Blood Collection Information</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="timer-container">
                        <div class="timer-display" id="timer">00:00:00</div>
                        <div class="timer-buttons">
                            <button type="button" id="startTimer">Start</button>
                            <button type="button" id="stopTimer" disabled>Stop</button>
                            <button type="button" id="resetTimer">Reset</button>
                        </div>
                        <!-- Hidden fields to store timestamps -->
                        <input type="hidden" name="start_time" id="start_time">
                        <input type="hidden" name="end_time" id="end_time">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="karmi">Karmi Blood Bag Type</label>
                            <select name="karmi" id="karmi" required onchange="updateNumberOfBags()">
                                <option value="None">None</option>
                                <option value="S">Single (S)</option>
                                <option value="D">Double (D) - Red Cell and Plasma Separation</option>
                                <option value="T">Triple (T) - Red Cell, Plasma, and Platelet Separation</option>
                                <option value="Q">Quadruple (Q) - Red Cell, Plasma, Platelet, and Cryoprecipitate</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="terumo">Terumo Blood Bag Type</label>
                            <select name="terumo" id="terumo" required onchange="updateNumberOfBags()">
                                <option value="None">None</option>
                                <option value="S">Single (S) - Basic Blood Collection</option>
                                <option value="D">Double (D) - Blood Component Separation</option>
                                <option value="T">Triple (T) - Multi-Component Separation</option>
                                <option value="Q">Quadruple (Q) - Full Component Separation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="special_bag">Special Blood Bag Type</label>
                            <select name="special_bag" id="special_bag" required>
                                <option value="None">None</option>
                                <option value="fk">Filter Kit (FK) - Leukocyte Reduction</option>
                                <option value="t&b">Top & Bottom (T&B) - Improved Separation Efficiency</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="apheresis">Apheresis Machine Type</label>
                            <select name="apheresis" id="apheresis" required>
                                <option value="none">None</option>
                                <option value="fres">Fresenius (Fres) - Automated Blood Collection</option>
                                <option value="ami">Amicus (Ami) - Platelet & Plasma Collection</option>
                                <option value="hae">Haemonetics (Hae) - Multi-Component Apheresis</option>
                                <option value="tri">Trima Accel (Tri) - Versatile Blood Component Collection</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="blood_type">Blood Component</label>
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
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount_blood_taken">Amount of Blood Taken (Units)</label>
                            <input type="text" name="amount_blood_taken" id="amount_blood_taken" required placeholder="e.g., 450">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="successful">Successful Collection</label>
                            <select name="successful" id="successful" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="management_done">Management Done By</label>
                            <input type="text" name="management_done" id="management_done" value="<?php echo htmlspecialchars($staff_name); ?>" readonly required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="donors_reaction">Donor's Reaction</label>
                        <textarea name="donors_reaction" id="donors_reaction" rows="4" required placeholder="Describe any reactions observed during the donation process..."></textarea>
                    </div>

                    <!-- Hidden field for number_of_bags -->
                    <input type="hidden" name="number_of_bags" id="number_of_bags" value="0">

                    <button type="submit" class="submit-btn">Submit Blood Collection</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let timer;
        let startTime;
        let elapsedTime = 0;
        let running = false;

        function updateNumberOfBags() {
            const karmiValue = document.getElementById('karmi').value;
            const terumoValue = document.getElementById('terumo').value;
            let bags = 0;

            // Check Karmi selection
            if (karmiValue !== 'None') {
                switch(karmiValue) {
                    case 'S': bags = 1; break;
                    case 'D': bags = 2; break;
                    case 'T': bags = 3; break;
                    case 'Q': bags = 4; break;
                }
            }

            // Check Terumo selection
            if (terumoValue !== 'None') {
                switch(terumoValue) {
                    case 'S': bags = 1; break;
                    case 'D': bags = 2; break;
                    case 'T': bags = 3; break;
                    case 'Q': bags = 4; break;
                }
            }

            document.getElementById('number_of_bags').value = bags;
        }

        function updateTimerDisplay() {
            const now = new Date().getTime();
            const diff = now - startTime + elapsedTime;
            
            let hours = Math.floor(diff / (1000 * 60 * 60));
            let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('timer').innerText = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        document.getElementById('startTimer').addEventListener('click', function () {
            if (!running) {
                startTime = new Date().getTime();
                document.getElementById('start_time').value = new Date().toISOString(); // Store start time
                timer = setInterval(updateTimerDisplay, 1000);
                running = true;
                document.getElementById('startTimer').disabled = true;
                document.getElementById('stopTimer').disabled = false;
            }
        });

        document.getElementById('stopTimer').addEventListener('click', function () {
            if (running) {
                clearInterval(timer);
                elapsedTime += new Date().getTime() - startTime;
                document.getElementById('end_time').value = new Date().toISOString(); // Store end time
                running = false;
                document.getElementById('startTimer').disabled = false;
                document.getElementById('stopTimer').disabled = true;
            }
        });

        document.getElementById('resetTimer').addEventListener('click', function () {
            clearInterval(timer);
            elapsedTime = 0;
            document.getElementById('timer').innerText = '00:00:00';
            document.getElementById('start_time').value = '';
            document.getElementById('end_time').value = '';
            running = false;
            document.getElementById('startTimer').disabled = false;
            document.getElementById('stopTimer').disabled = true;
        });
    </script>
</body>
</html>

