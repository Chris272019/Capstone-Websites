<?php
// Start the session
session_start();
$message = isset($_SESSION['message']) ? $_SESSION['message'] : "";
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";

// Clear session messages after display
unset($_SESSION['message'], $_SESSION['error']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard - Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: #f5f7fa;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #c10000 0%, #414141 100%);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 1.8em;
            color: #e74c3c;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin: 5px 0;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .nav-links a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #e74c3c;
        }

        .nav-links i {
            width: 25px;
            margin-right: 10px;
            font-size: 1.2em;
        }

        .nav-links span {
            font-size: 0.95em;
            font-weight: 500;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hospital-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .hospital-info {
            flex: 1;
        }

        .hospital-name {
            font-weight: 500;
            font-size: 0.9em;
        }

        .hospital-role {
            font-size: 0.8em;
            opacity: 0.8;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgb(255 0 0 / 80%);
            text-decoration: none;
            padding: 10px 20px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            color: #e74c3c;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 1.8em;
            font-weight: 600;
        }

        .welcome-text {
            color: #7f8c8d;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h2 span,
            .nav-links span,
            .hospital-info,
            .logout-btn span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .nav-links a {
                padding: 15px;
                justify-content: center;
            }

            .nav-links i {
                margin: 0;
                font-size: 1.4em;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            width: 60%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            border: 2px solid #c10000;
            position: relative;
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #c10000, #ff4d4d, #c10000);
        }

        .close {
            color: #c10000;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #ff4d4d;
        }

        .modal-body {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .modal-body .mb-3 {
            margin-bottom: 12px;
            position: relative;
        }

        .modal-body label {
            font-size: 0.9em;
            margin-bottom: 6px;
            display: block;
            color: #333;
            font-weight: 500;
        }

        .modal-body input,
        .modal-body select,
        .modal-body textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9em;
            transition: all 0.3s;
            background-color: #fff;
        }

        .modal-body input:focus,
        .modal-body select:focus,
        .modal-body textarea:focus {
            border-color: #c10000;
            box-shadow: 0 0 0 2px rgba(193, 0, 0, 0.1);
            outline: none;
        }

        .modal-body textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal h2 {
            color: #c10000;
            font-size: 1.6em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal h2::before {
            content: '🩸';
            font-size: 1.2em;
        }

        /* Blood type selector styling */
        .blood-type-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 5px;
        }

        .blood-type-option {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #fff;
        }

        .blood-type-option:hover {
            background-color: #fff5f5;
            border-color: #c10000;
        }

        .blood-type-option.selected {
            background-color: #c10000;
            color: white;
            border-color: #c10000;
        }

        /* Submit button styling */
        .modal button[type="submit"] {
            background: linear-gradient(45deg, #c10000, #ff4d4d);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .modal button[type="submit"]:hover {
            background: linear-gradient(45deg, #ff4d4d, #c10000);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(193, 0, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 10% auto;
            }

            .modal-body {
                grid-template-columns: 1fr;
            }

            .blood-type-selector {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Emergency indicator */
        .emergency-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #c10000;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .emergency-indicator::before {
            content: '⚠️';
        }
    </style>
</head>
<body>
    <!-- Display success or error messages -->
    <?php if ($message): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <i class="fas fa-hospital"></i>
                <span>Blood Bank</span>
            </h2>
        </div>
        <ul class="nav-links">
            <li>
                <a href="#" id="makeRequestBtn" data-section="makeRequest" onclick="openModal()">
                    <i class="fas fa-plus-circle"></i>
                    <span>Make a Request</span>
                </a>
            </li>
            <li>
                <a href="#" data-section="bloodRequests" onclick="loadContent('bloodRequests')">
                    <i class="fas fa-history"></i>
                    <span>Request History</span>
                </a>
            </li>
            <li>
                <a href="#" data-section="reports" onclick="loadReport()">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="#" data-section="inventory" onclick="loadInventory(); setActiveLink(this);">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="hospital-profile">
                <div class="hospital-info">
                    <div class="hospital-name"><?php echo isset($_SESSION['hospital_name']) ? htmlspecialchars($_SESSION['hospital_name']) : 'Hospital'; ?></div>
                    <div class="hospital-role">Hospital Account</div>
                </div>
            </div>
            <a href="hospital_logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Hospital Dashboard</h1>
            <div class="welcome-text">
                Welcome back, <?php echo isset($_SESSION['hospital_name']) ? htmlspecialchars($_SESSION['hospital_name']) : 'Hospital'; ?>!
            </div>
        </div>

        <!-- Blood Requests Container -->
        <div id="bloodRequestsContainer" style="display: none;"></div>

        <!-- Report Container -->
        <div id="reportContainer" style="display: none;"></div>

        <!-- Inventory Container -->
        <div id="inventoryContainer" style="display: none;"></div>
    </div>

    <!-- Modal for Making a Request -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Make a Request</h2>
            <form id="bloodRequestForm" method="POST" action="process_request.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="surname" class="form-label">Surname</label>
                        <input type="text" class="form-control" id="surname" name="surname">
                    </div>
                    <div class="mb-3">
                        <label for="firstname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstname" name="firstname">
                    </div>
                    <div class="mb-3">
                        <label for="middlename" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middlename" name="middlename">
                    </div>
                    <div class="mb-3">
                        <label for="birthdate" class="form-label">Birthdate</label>
                        <input type="date" class="form-control" id="birthdate" name="birthdate" onchange="calculateAge()">
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sex</label>
                        <div>
                            <input type="radio" id="male" name="sex" value="Male">
                            <label for="male">Male</label>
                            <input type="radio" id="female" name="sex" value="Female">
                            <label for="female">Female</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="hospital" class="form-label">Hospital</label>
                        <input type="text" class="form-control" id="hospital" name="hospital" value="<?php echo isset($_SESSION['hospital_name']) ? htmlspecialchars($_SESSION['hospital_name']) : ''; ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="attending_physician" class="form-label">Attending Physician</label>
                        <input type="text" class="form-control" id="attending_physician" name="attending_physician">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ward</label>
                        <div>
                            <input type="radio" id="pay" name="ward" value="Pay">
                            <label for="pay">Pay</label>
                            <input type="radio" id="charity" name="ward" value="Charity">
                            <label for="charity">Charity</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="room_no" class="form-label">Room No.</label>
                        <input type="text" class="form-control" id="room_no" name="room_no">
                    </div>
                    <div class="mb-3">
                        <label for="tel_no" class="form-label">Tel. No.</label>
                        <input type="text" class="form-control" id="tel_no" name="tel_no">
                    </div>
                    <div class="mb-3">
                        <label for="clinical_diagnosis" class="form-label">Clinical Diagnosis</label>
                        <textarea class="form-control" id="clinical_diagnosis" name="clinical_diagnosis" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="when" class="form-label">When</label>
                        <input type="date" class="form-control" id="when" name="when">
                    </div>
                    <div class="mb-3">
                        <label for="where" class="form-label">Where</label>
                        <input type="text" class="form-control" id="where" name="where" value="<?php echo isset($_SESSION['hospital_name']) ? htmlspecialchars($_SESSION['hospital_name']) : ''; ?>" readonly>
                    </div>
                </div>
                <br><br><br>

                <!-- Request Type Dropdown -->
                <label for="requestType">Request Type:</label><br>
                <select id="requestType" name="requestType" onchange="showUnits()">
                    <option value="wholeBlood">Whole Blood</option>
                    <option value="packedRBC">Packed RBC</option>
                    <option value="washedRBC">Washed RBC</option>
                    <option value="buffyCoatPoorRBC">Buffy Coat-Poor RBC</option>
                    <option value="plateletConcentrate">Platelet Concentrate</option>
                    <option value="apheresisPlatelets">Apheresis Platelets</option>
                    <option value="leukocytePoorPlatelets">Leukocyte-Poor Platelets</option>
                    <option value="freshFrozenPlasma">Fresh Frozen Plasma</option>
                    <option value="leukocytePoorFreshFrozenPlasma">Leukocyte-Poor Fresh Frozen Plasma</option>
                    <option value="cryoprecipitate">Cryoprecipitate</option>
                </select><br><br>

                <!-- Dynamic Units Input Fields (Initially Hidden) -->
                

                <!-- Whole Blood Section -->
                <div id="wholeBloodUnits" style="display: none;">
                    <label for="wholeBloodUnitsInput">Units of Whole Blood:</label>
                    <input type="number" id="wholeBloodUnitsInput" name="wholeBloodUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <label style="display: block;">
                        <input type="radio" id="WB1" name="wholeBloodType" value="WB1"> WB1: Active bleeding with atleast one of the following<br>
                        a. Loss of over 15% blood volume<br>
                        b. Hgb less than 9g/L<br>
                        c. Blood pressure decrease over 20% or less than 90mm Hg.systolic<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="WB2" name="wholeBloodType" value="WB2"> WB2: Others please specify
                    </label>
                    
                    <!-- Reason text box (Initially hidden) -->
                    <div id="reasonDiv" style="display: none;">
                        <label for="reasonText">Please provide the reason for selecting WB2:</label><br>
                        <textarea id="reasonText" name="reasonText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <div id="packedRBCUnits" style="display: none;">
                    <label for="packedRBCUnitsInput">Units of Packed RBC:</label>
                    <input type="number" id="packedRBCUnitsInput" name="packedRBCUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <label style="display: block;">
                        <input type="radio" id="R1" name="packedRBCType" value="R1"> R1: Hgb less than 8g/L or Hct less than 24% (if not due to treatable cause).<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="R2" name="packedRBCType" value="R2"> R2: Patients receiving general anesthesia if: <br>

a. Pre-operative Hgb less than 8g/L or Hct less than 24%,<br>

b. Major bloodletting operation and Hgb less than 10g/L <br>

c. Hct less than 30%;<br>d. Signs of hemodynamic instability or inadequate oxygen carrying capacity (symptomatic anemia).<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="R3" name="packedRBCType" value="R3"> R3: Symptomatic anemia regardless of Hgb level (dyspnea, syncope, postural hypotension, tachycardia, chest pains, and TIA).<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="R4" name="packedRBCType" value="R4"> R4: Hgb less than 8g/L or Hct less than 24% with concomitant hemorrhage, COPD, CAD, hemoglobinopathy and sepsis.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="R5" name="packedRBCType" value="R5"> R5: Others: Please specify
                    </label>

                    <!-- Reason text box (Initially hidden) -->
                    <div id="reasonRBCDiv" style="display: none;">
                        <label for="reasonRBCText">Please provide the reason for selecting R5 Packed RBC:</label><br>
                        <textarea id="reasonRBCText" name="reasonRBCText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <div id="washedRBCUnits" style="display: none;">
                    <label for="washedRBCUnitsInput">Units of Washed RBC:</label>
                    <input type="number" id="washedRBCUnitsInput" name="washedRBCUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <!-- Add radio buttons for WP1 to WP4 -->
                    <label style="display: block;">
                        <input type="radio" id="WashedWP1" name="washedRBCType" value="WP1"> WP1: History of previous severe allergic transfusion reactions or anaphylactoid reactions in immunocompromised patients.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="WashedWP2" name="washedRBCType" value="WP2"> WP2: Transfusion of group "O" blood during emergencies when the specific blood is not immediately available.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="WashedWP3" name="washedRBCType" value="WP3"> WP3: Paroxysmal nocturnal hemoglobinuria.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="WashedWP4" name="washedRBCType" value="WP4"> WP4: Others: Please specify
                    </label>

                    <!-- Reason text box (Initially hidden) -->
                    <div id="reasonWashedRBCDiv" style="display: none;">
                        <label for="reasonWashedRBCText">Please provide the reason for selecting WP4 Washed RBC:</label><br>
                        <textarea id="reasonWashedRBCText" name="reasonWashedRBCText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <!-- Buffy Coat-Poor RBC Section -->
                <div id="buffyCoatPoorRBCUnits" style="display: none;">
                    <label for="buffyCoatPoorRBCUnitsInput">Units of Buffy Coat-Poor RBC:</label>
                    <input type="number" id="buffyCoatPoorRBCUnitsInput" name="buffyCoatPoorRBCUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <!-- Add radio buttons for WP1 to WP4 -->
                    <label style="display: block;">
                        <input type="radio" id="BuffyWP1" name="buffyRBCType" value="WP1"> WP1: History of previous severe allergic transfusion reactions or anaphylactoid reactions in immunocompromised patients.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="BuffyWP2" name="buffyRBCType" value="WP2"> WP2: Transfusion of group "O" blood during emergencies when the specific blood is not immediately available.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="BuffyWP3" name="buffyRBCType" value="WP3"> WP3: Paroxysmal nocturnal hemoglobinuria.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="BuffyWP4" name="buffyRBCType" value="WP4"> WP4: Others: Please specify
                    </label>

                    <!-- Reason text box (Initially hidden) -->
                    <div id="reasonBuffyRBCDiv" style="display: none;">
                        <label for="reasonBuffyRBCText">Please provide the reason for selecting WP4 Buffy Coat-Poor RBC:</label><br>
                        <textarea id="reasonBuffyRBCText" name="reasonBuffyRBCText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <!-- Platelet Concentrate Section -->
                <div id="plateletConcentrateUnits" style="display: none;">
                    <label for="plateletConcentrateUnitsInput">Units of Platelet Concentrate:</label>
                    <input type="number" id="plateletConcentrateUnitsInput" name="plateletConcentrateUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <!-- Radio buttons for Platelet Concentrate -->
                    <label style="display: block;">
                        <input type="radio" id="P1" name="plateletConcentrateType" value="P1"> P1: Prophylactic administration with count $20,000 and not due to TTP, ITP and HUS.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P2" name="plateletConcentrateType" value="P2"> P2: Active bleeding with count $50,000.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P3" name="plateletConcentrateType" value="P3"> P3: Platelet count $50,000 and patient to undergo invasive procedure within 8 hrs.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P4" name="plateletConcentrateType" value="P4"> P4: Platelet count $100,000 if surgery is on critical area e g. eye, brain, etc.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P5" name="plateletConcentrateType" value="P5"> P5: Massive transfusion with diffuse microvascular bleeding and no time to obtain platelet count<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P6" name="plateletConcentrateType" value="P6"> P6: Others: Please specify
                    </label>

                    <!-- Reason text box (Initially hidden) -->
                    <div id="reasonPlateletConcentrateDiv" style="display: none;">
                        <label for="reasonPlateletConcentrateText">Please provide the reason for selecting P6 Platelet Concentrate:</label><br>
                        <textarea id="reasonPlateletConcentrateText" name="reasonPlateletConcentrateText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <!-- Apheresis Platelets Units -->
                <div id="apheresisPlateletsUnits" style="display: none;">
                    <label for="apheresisPlateletsUnitsInput">Units of Apheresis Platelets:</label><br>
                    
                    <!-- Radio buttons for Apheresis Platelets Units -->
                    <label style="display: block;">
                        <input type="radio" id="4units" name="apheresisPlateletsUnits" value="4 units" <?php if(isset($_POST['apheresisPlateletsUnits']) && $_POST['apheresisPlateletsUnits'] == '4') echo 'checked'; ?>>
                        4 Units
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="8units" name="apheresisPlateletsUnits" value="8 units" <?php if(isset($_POST['apheresisPlateletsUnits']) && $_POST['apheresisPlateletsUnits'] == '8') echo 'checked'; ?>>
                        8 Units
                    </label>
                    <br>

                    <!-- Radio buttons for Apheresis Platelets Types -->
                    <label style="display: block;">
                        <input type="radio" id="P1" name="apheresisPlateletType" value="P1">
                        P1: Prophylactic administration with count $20,000 and not due to TTP, ITP and HUS.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P2" name="apheresisPlateletType" value="P2">
                        P2: Active bleeding with count $50,000.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P3" name="apheresisPlateletType" value="P3">
                        P3: Platelet count $50,000 and patient to undergo invasive procedure within 8 hrs.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P4" name="apheresisPlateletType" value="P4">
                        P4: Platelet count $100,000 if surgery is on critical area e g. eye, brain, etc.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P5" name="apheresisPlateletType" value="P5">
                        P5: Massive transfusion with diffuse microvascular bleeding and no time to obtain platelet count<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P6" name="apheresisPlateletType" value="P6">
                        P6: Others: Please specify
                    </label>

                    <!-- Reason text box for P6 (Initially hidden) -->
                    <div id="reasonApheresisPlateletsDiv" style="display: none;">
                        <label for="reasonApheresisPlateletsText">Please provide the reason for selecting P6 Apheresis Platelets:</label><br>
                        <textarea id="reasonApheresisPlateletsText" name="reasonApheresisPlateletsText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <!-- Leukocyte-Poor Platelets Units -->
                <div id="leukocytePoorPlateletsUnits" style="display: none;">
                    <label for="leukocytePoorPlateletsUnitsInput">Units of Leukocyte-Poor Platelets:</label>
                    <input type="number" id="leukocytePoorPlateletsUnitsInput" name="leukocytePoorPlateletsUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <!-- Radio buttons for Leukocyte-Poor Platelets Types -->
                    <label style="display: block;">
                        <input type="radio" id="P1" name="leukocytePoorPlateletType" value="P1">
                        P1: Prophylactic administration with count $20,000 and not due to TTP, ITP and HUS.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P2" name="leukocytePoorPlateletType" value="P2">
                        P2: Active bleeding with count $50,000.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P3" name="leukocytePoorPlateletType" value="P3">
                        P3: Platelet count $50,000 and patient to undergo invasive procedure within 8 hrs.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P4" name="leukocytePoorPlateletType" value="P4">
                        P4: Platelet count $100,000 if surgery is on critical area e g. eye, brain, etc.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P5" name="leukocytePoorPlateletType" value="P5">
                        P5: Massive transfusion with diffuse microvascular bleeding and no time to obtain platelet count<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="P6" name="leukocytePoorPlateletType" value="P6">
                        P6: Others: Please specify
                    </label>

                    <!-- Reason text box for P6 (Initially hidden) -->
                    <div id="reasonLeukocytePlateletsDiv" style="display: none;">
                        <label for="reasonLeukocytePlateletsText">Please provide the reason for selecting P6 Leukocyte-Poor Platelets:</label><br>
                        <textarea id="reasonLeukocytePlateletsText" name="reasonLeukocytePlateletsText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <!-- Fresh Frozen Plasma Units -->
                <div id="freshFrozenPlasmaUnits" style="display: none;">
                    <label for="freshFrozenPlasmaUnitsInput">Units of Fresh Frozen Plasma:</label>
                    <input type="number" id="freshFrozenPlasmaUnitsInput" name="freshFrozenPlasmaUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <!-- Radio buttons for Fresh Frozen Plasma Types -->
                    <label style="display: block;">
                        <input type="radio" id="F1" name="freshFrozenPlasmaType" value="F1">
                        F1: PT or PTT >1.5 times mid-normal range within 8 hrs. of transfusion. (PT >17 secs. Or PTT >47 secs.)<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F2" name="freshFrozenPlasmaType" value="F2">
                        F2: Specific factor deficiencies not treatable with cryoprecipitate.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F3" name="freshFrozenPlasmaType" value="F3">
                        F3: Reversal of Coumadin anticoagulation in patients who are bleeding and not treatable with Vitamin K.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F4" name="freshFrozenPlasmaType" value="F4">
                        F4: Treatment of TTP.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F5" name="freshFrozenPlasmaType" value="F5">
                        F5: Others: Please specify
                    </label>

                    <!-- Reason text box for F5 (Initially hidden) -->
                    <div id="reasonFreshFrozenPlasmaDiv" style="display: none;">
                        <label for="reasonFreshFrozenPlasmaText">Please provide the reason for selecting F5 Fresh Frozen Plasma:</label><br>
                        <textarea id="reasonFreshFrozenPlasmaText" name="reasonFreshFrozenPlasmaText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>

                <!-- Leukocyte-Poor Fresh Frozen Plasma Units -->
                <div id="leukocytePoorFreshFrozenPlasmaUnits" style="display: none;">
                    <label for="leukocytePoorFreshFrozenPlasmaUnitsInput">Units of Leukocyte-Poor Fresh Frozen Plasma:</label>
                    <input type="number" id="leukocytePoorFreshFrozenPlasmaUnitsInput" name="leukocytePoorFreshFrozenPlasmaUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <!-- Radio buttons for Leukocyte-Poor Fresh Frozen Plasma -->
                    <label style="display: block;">
                        <input type="radio" id="F1" name="leukocytePoorFreshFrozenPlasmaType" value="F1">
                        F1: PT or PTT >1.5 times mid-normal range within 8 hrs. of transfusion. (PT >17 secs. Or PTT >47 secs.)<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F2" name="leukocytePoorFreshFrozenPlasmaType" value="F2">
                        F2: Specific factor deficiencies not treatable with cryoprecipitate.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F3" name="leukocytePoorFreshFrozenPlasmaType" value="F3">
                        F3: Reversal of Coumadin anticoagulation in patients who are bleeding and not treatable with Vitamin K.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F4" name="leukocytePoorFreshFrozenPlasmaType" value="F4">
                        F4: Treatment of TTP.<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="F5" name="leukocytePoorFreshFrozenPlasmaType" value="F5" onclick="displayReasonBox()">
                        F5: Others: Please specify
                    </label>

                    <!-- Reason Textbox for F5 -->
                    <div id="reasonleukocytePoorFreshFrozenPlasmaDiv" style="display: none;">
                        <label for="reasonleukocytePoorFreshFrozenPlasmaText">Please provide a reason:</label><br>
                        <textarea id="reasonleukocytePoorFreshFrozenPlasmaDText" name="reasonleukocytePoorFreshFrozenPlasmaDText" rows="4" cols="50" placeholder="Enter reason here"></textarea><br><br>
                    </div>
                </div>

                <!-- Cryoprecipitate Units -->
                <div id="cryoprecipitateUnits" style="display: none;">
                    <label for="cryoprecipitateUnitsInput">Units of Cryoprecipitate:</label>
                    <input type="number" id="cryoprecipitateUnitsInput" name="cryoprecipitateUnitsInput" placeholder="Enter units" min="1"><br><br>

                    <!-- Radio buttons for Cryoprecipitate -->
                    <label style="display: block;">
                        <input type="radio" id="C1" name="cryoprecipitateType" value="C1">
                        C1: Significant hypofibrinogemia (<100 mg/dl).<br>
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="C2" name="cryoprecipitateType" value="C2">
                        C2: Hemophilia
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="C3" name="cryoprecipitateType" value="C3">
                        C3: Von Willebrand disease or uremic bleeding with
                    </label>
                    <label style="display: block;">
                        <input type="radio" id="C4" name="cryoprecipitateType" value="C4">
                        C4: Others: Please specify
                    </label>
                    <div id="reasonForC4" style="display: none;">
                        <label for="reasoncryoprecipitateText">Please provide a reason:</label><br>
                        <textarea id="reasoncryoprecipitateText" name="reasoncryoprecipitateText" rows="4" cols="50" placeholder="Enter reason here"></textarea><br><br>
                    </div>
                </div>

                <button type="submit">Submit Request</button>
            </form>
        </div>
    </div>

    <script src="js/hospital.js"></script>
    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.main-content');
        sidebar.classList.toggle('open');
        content.classList.toggle('shifted');
    }

    function loadReport() {
        // Hide other containers
        document.getElementById('bloodRequestsContainer').style.display = 'none';
        document.getElementById('inventoryContainer').style.display = 'none';
        document.getElementById('reportContainer').style.display = 'block';

        // Set active state
        const reportLink = document.querySelector('a[data-section="reports"]');
        setActiveLink(reportLink);

        // Show loading state
        document.getElementById('reportContainer').innerHTML = '<div class="alert alert-info">Loading report data...</div>';

        // Fetch and display report data
        fetch('report.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(data => {
                console.log('Received report data'); // Debug log
                
                // Insert the HTML content
                document.getElementById('reportContainer').innerHTML = data;
                
                // Make sure Chart.js is loaded
                if (typeof Chart === 'undefined') {
                    console.log('Chart.js not loaded, loading it now');
                    const chartScript = document.createElement('script');
                    chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                    chartScript.onload = initializeCharts;
                    document.head.appendChild(chartScript);
                } else {
                    console.log('Chart.js already loaded');
                    // Initialize charts after content is loaded
                    initializeCharts();
                }
            })
            .catch(error => {
                console.error('Error fetching report data:', error);
                document.getElementById('reportContainer').innerHTML = 
                    `<div class="alert alert-danger">
                        Error loading report data. Please try again.<br>
                        Error details: ${error.message}
                    </div>`;
            });
    }
    
    // Function to initialize charts after report content is loaded
    function initializeCharts() {
        console.log('Initializing charts');
        
        // Wait for a short time to ensure DOM is completely updated
        setTimeout(() => {
            // Check if the report chart initialization functions exist
            if (typeof initializeReportCharts === 'function') {
                console.log('Calling report chart initialization function');
                initializeReportCharts();
            } else {
                console.error('Report chart initialization function not found');
                
                // Execute any script content from the report.php 
                // by extracting it and creating a new script element
                const scriptElements = document.getElementById('reportContainer').querySelectorAll('script');
                console.log('Found', scriptElements.length, 'script elements');
                
                scriptElements.forEach(script => {
                    if (script.innerText && script.innerText.trim() !== '') {
                        const newScript = document.createElement('script');
                        newScript.textContent = script.innerText;
                        document.body.appendChild(newScript);
                        console.log('Executed script element');
                    }
                });
                
                // Try again after scripts have executed
                setTimeout(() => {
                    if (typeof initializeReportCharts === 'function') {
                        initializeReportCharts();
                        console.log('Report charts initialized on second attempt');
                    }
                }, 500);
            }
            
            // Initialize report filters if the function exists
            if (typeof initializeReportFilters === 'function') {
                console.log('Calling report filter initialization function');
                initializeReportFilters();
            }
        }, 200);
    }

    function loadContent(contentType) {
        if (contentType === 'bloodRequests') {
            // Hide other containers
            document.getElementById('reportContainer').style.display = 'none';
            document.getElementById('inventoryContainer').style.display = 'none';
            document.getElementById('bloodRequestsContainer').style.display = 'block';
            
            // Set active state
            const requestsLink = document.querySelector('a[data-section="bloodRequests"]');
            setActiveLink(requestsLink);

            // Show loading state
            document.getElementById('bloodRequestsContainer').innerHTML = '<div class="alert alert-info">Loading request history...</div>';

            fetch('process_history_request.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('bloodRequestsContainer').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    document.getElementById('bloodRequestsContainer').innerHTML = 
                        `<div class="alert alert-danger">
                            Error loading request history. Please try again.<br>
                            Error details: ${error.message}
                        </div>`;
                });
        }
    }

    function loadInventory() {
        // Hide other containers
        document.getElementById('reportContainer').style.display = 'none';
        document.getElementById('bloodRequestsContainer').style.display = 'none';
        document.getElementById('inventoryContainer').style.display = 'block';

        // Set active state
        const inventoryLink = document.querySelector('a[data-section="inventory"]');
        setActiveLink(inventoryLink);

        // Show loading state
        document.getElementById('inventoryContainer').innerHTML = '<div class="alert alert-info">Loading inventory data...</div>';

        // Fetch and display inventory data
        fetch('process_inventory.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(data => {
                console.log('Received data:', data); // Debug log
                
                // Insert the HTML content
                document.getElementById('inventoryContainer').innerHTML = data;
                
                // Execute any scripts in the fetched content
                const scriptTags = document.getElementById('inventoryContainer').querySelectorAll('script');
                scriptTags.forEach(script => {
                    // Create a new script element
                    const newScript = document.createElement('script');
                    
                    // Copy all attributes from original script
                    Array.from(script.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    
                    // Copy the content
                    newScript.textContent = script.textContent;
                    
                    // Remove the original script
                    script.parentNode.removeChild(script);
                    
                    // Add the new script to execute it
                    document.getElementById('inventoryContainer').appendChild(newScript);
                });
                
                // Initialize any SweetAlert buttons
                initializeButtonEvents();
            })
            .catch(error => {
                console.error('Error fetching inventory data:', error);
                document.getElementById('inventoryContainer').innerHTML = 
                    `<div class="alert alert-danger">
                        Error loading inventory data. Please try again.<br>
                        Error details: ${error.message}
                    </div>`;
            });
    }

    function closeContent() {
        document.getElementById('reportContainer').style.display = 'none';
        document.getElementById('bloodRequestsContainer').style.display = 'none';
        document.getElementById('inventoryContainer').style.display = 'none';
    }

    function setActiveLink(element) {
        // Remove active class from all sidebar links
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        sidebarLinks.forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to the clicked link
        if (element) {
            element.classList.add('active');
            
            // Save active section to localStorage
            if (element.dataset.section) {
                localStorage.setItem('activeSidebarSection', element.dataset.section);
            }
        }
    }

    // When the page loads, check if there's a saved active section
    document.addEventListener('DOMContentLoaded', function() {
        // Get the active section from localStorage
        const activeSection = localStorage.getItem('activeSidebarSection');
        
        // If there's a saved section, activate that link
        if (activeSection) {
            const activeLink = document.querySelector(`.sidebar a[data-section="${activeSection}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
                
                // Also load the content for that section
                if (activeSection === 'reports') {
                    loadReport();
                } else if (activeSection === 'bloodRequests') {
                    loadContent('bloodRequests');
                } else if (activeSection === 'inventory') {
                    loadInventory();
                }
            }
        } else {
            // Default to showing inventory if no section is active
            const inventoryLink = document.querySelector('a[data-section="inventory"]');
            setActiveLink(inventoryLink);
            loadInventory();
        }
    });

    function openModal() {
        document.getElementById('myModal').style.display = 'block';
        
        // Set active state for the make request button
        const requestBtn = document.querySelector('a[data-section="makeRequest"]');
        setActiveLink(requestBtn);
    }

    function closeModal() {
        document.getElementById('myModal').style.display = 'none';
    }

    function calculateAge() {
        const birthdateInput = document.getElementById('birthdate');
        const ageInput = document.getElementById('age');
        
        const birthdate = new Date(birthdateInput.value);
        const today = new Date();
        
        if (birthdate) {
            let age = today.getFullYear() - birthdate.getFullYear();
            const monthDifference = today.getMonth() - birthdate.getMonth();
            
            if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }
            
            ageInput.value = age;
        } else {
            ageInput.value = '';
        }
    }

    function showUnits() {
        const unitSections = document.querySelectorAll('[id$="Units"]');
        unitSections.forEach(section => section.style.display = 'none');

        const requestType = document.getElementById('requestType').value;
        const selectedSection = document.getElementById(requestType + 'Units');
        if (selectedSection) {
            selectedSection.style.display = 'block';
        }
    }

    // Function to initialize button events after content is loaded
    function initializeButtonEvents() {
        // Add click events to deliver buttons
        const deliverButtons = document.querySelectorAll('.deliver-btn');
        console.log('Initializing button events for', deliverButtons.length, 'buttons');
        
        deliverButtons.forEach(button => {
            button.addEventListener('click', function() {
                const inventoryId = this.getAttribute('data-id');
                const currentVolume = this.getAttribute('data-volume');
                
                console.log('Button clicked with ID:', inventoryId, 'Volume:', currentVolume);
                
                // Show the SweetAlert modal
                Swal.fire({
                    title: 'Confirm Delivery',
                    html: `
                        <div style="text-align: center; margin-bottom: 25px;">
                            <div style="display: inline-block; width: 70px; height: 70px; border-radius: 50%; border: 2px solid #f8bb86; margin-bottom: 15px;">
                                <i class="fas fa-exclamation" style="color: #f8bb86; font-size: 40px; line-height: 70px;"></i>
                            </div>
                            <p style="font-size: 16px; color: #555; margin-top: 10px;">Are you sure you want to mark this item as Reserved?</p>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <select id="hospital-select" class="swal2-input" style="width: 100%; max-width: 425px; margin: 10px auto;">
                                <option value="">Select Hospital</option>
                                <option value="ADMIN_7749" selected>ADMIN_7749</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <input type="number" id="blood-volume" class="swal2-input" 
                                placeholder="Enter Volume (ml)" value="450" 
                                style="width: 100%; max-width: 425px; margin: 0 auto;" 
                                oninput="calculateBags(this.value)">
                            <p style="margin-top: 5px; font-size: 12px; color: #666; text-align: center;">
                                Standard blood bag contains 450ml
                            </p>
                        </div>
                        <div style="text-align: center; margin: 20px 0; padding: 15px; background-color: #e3f7fc; border-radius: 8px; width: 100%; max-width: 425px; margin: 15px auto;">
                            <p style="margin: 0; font-weight: bold; color: #0c5460;">
                                Calculated Bags: <span id="calculated-bags">1 bag</span>
                            </p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Confirm Delivery',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    customClass: {
                        container: 'custom-swal-container',
                        popup: 'custom-swal-popup',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-swal-cancel'
                    },
                    focusConfirm: false,
                    didOpen: function() {
                        // Calculate bags on load to ensure it's initialized correctly
                        calculateBags(document.getElementById('blood-volume').value);
                    },
                    preConfirm: function() {
                        var hospitalId = document.getElementById('hospital-select').value;
                        var bloodVolume = document.getElementById('blood-volume').value;
                        
                        if (!hospitalId) {
                            Swal.showValidationMessage('Please select a hospital');
                            return false;
                        }
                        
                        if (!bloodVolume || bloodVolume <= 0) {
                            Swal.showValidationMessage('Valid blood volume is required');
                            return false;
                        }
                        
                        return { adminId: hospitalId, bloodVolume: bloodVolume, inventoryId: inventoryId };
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        // Send data to server to process the delivery
                        $.ajax({
                            url: 'deliver_blood.php',
                            type: 'POST',
                            data: {
                                inventory_id: result.value.inventoryId,
                                admin_id: result.value.adminId,
                                blood_volume: result.value.bloodVolume
                            },
                            success: function(response) {
                                try {
                                    var data = JSON.parse(response);
                                    if (data.success) {
                                        Swal.fire(
                                            'Success!',
                                            data.message || 'Blood delivery processed successfully!',
                                            'success'
                                        ).then(function() {
                                            // Reload the inventory data
                                            loadInventory();
                                        });
                                    } else {
                                        Swal.fire(
                                            'Error!',
                                            data.message || 'An error occurred during delivery process.',
                                            'error'
                                        );
                                    }
                                } catch (e) {
                                    Swal.fire(
                                        'Error!',
                                        'An unexpected error occurred during delivery process.',
                                        'error'
                                    );
                                    console.error('Error parsing response:', e, response);
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire(
                                    'Error!',
                                    'Server error: ' + error,
                                    'error'
                                );
                                console.error('AJAX error:', xhr, status, error);
                            }
                        });
                    }
                });
            });
        });
    }
    
    // Function to calculate bags from volume
    function calculateBags(volume) {
        var bagsElement = document.getElementById('calculated-bags');
        if (!bagsElement) {
            console.error('Could not find calculated-bags element');
            return;
        }
        
        if (!volume || volume <= 0) {
            bagsElement.textContent = '0 bags';
            return;
        }
        
        var standardBagVolume = 450; // ml
        var bags = Math.ceil(volume / standardBagVolume);
        bagsElement.textContent = bags + ' bag' + (bags !== 1 ? 's' : '');
    }
    </script>
</body>
</html>

