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
    <title>Hospital Dashboard - Blood Donation System</title>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/hospital.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
 

</head>
<style>
    body {
            transition: margin-left 0.3s ease;
        }

        .sidebar {
            width: 250px;
            position: fixed;
            left: -250px;
            top: 0;
            height: 100%;
            background-color:rgb(173, 20, 20);
            transition: left 0.3s ease;
            z-index: 1000;
            padding-top: 60px;
        }

        .sidebar.open {
            left: 0;
        }

        .content {
            transition: margin-left 0.3s ease;
            padding: 20px;
        }

        .content.shifted {
            margin-left: 250px;
        }

        .sidebar-toggle {
            position: fixed;
            left: 10px;
            top: 10px;
            z-index: 1001;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        @media screen and (max-width: 768px) {
            .content.shifted {
                margin-left: 0;
            }

            .sidebar {
                width: 100%;
                left: -100%;
            }

            .sidebar.open {
                left: 0;
            }
        }
    /* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #f8f8f8;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 700px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Header styles */
.modal-content h2 {
    color: #e60000;
    border-bottom: 2px solid #e60000;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

/* Form styles */
.modal-body {
    display: grid;
    gap: 15px;
}

.mb-3 {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

/* Radio button styles */
input[type="radio"] {
    margin-right: 5px;
}

/* Textarea styles */
textarea.form-control {
    resize: vertical;
}

/* Blood type section styles */
#requestType {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 15px;
}

/* Submit button styles */
button[type="submit"] {
    background-color: #e60000;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: #cc0000;
}

/* Close button styles */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

/* Responsive design */
@media screen and (max-width: 600px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
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

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">&#9776;</button>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <a href="#" id="makeRequestBtn" onclick="openModal()"><i class="fas fa-plus-circle"></i> Make a Request</a>
        <a href="#" onclick="loadContent('bloodRequests')"><i class="fas fa-tint"></i> Blood Requests</a>
        <a href="#" onclick="loadReport()"><i class="fas fa-chart-line"></i> Reports</a>
        <a href="hospital_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Header Section -->
    <header>
        <h1>Hospital Dashboard</h1>
        <p>Welcome to the Blood Donation System</p>
    </header>

    <!-- Sidebar Navigation -->
    <!-- Toggle Sidebar Button -->


    <!-- Modal for Making a Request -->
<!-- Modal -->
<div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Make a Request</h2>
            <form id="bloodRequestForm" method="POST" action="process_request.php">





            <div class="modal-body">
          <div class="mb-3">
            <label for="surname" class="form-label">Surname</label>
            <input type="text" class="form-control" id="surname" name="surname" >
          </div>
          <div class="mb-3">
            <label for="firstname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstname" name="firstname" >
          </div>
          <div class="mb-3">
            <label for="middlename" class="form-label">Middle Name</label>
            <input type="text" class="form-control" id="middlename" name="middlename">
          </div>
          <div class="mb-3">
            <label for="age" class="form-label">Age</label>
            <input type="number" class="form-control" id="age" name="age" >
          </div>
          <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input type="date" class="form-control" id="birthdate" name="birthdate" >
          </div>
          <div class="mb-3">
            <label class="form-label">Sex</label>
            <div>
              <input type="radio" id="male" name="sex" value="Male" >
              <label for="male">Male</label>
              <input type="radio" id="female" name="sex" value="Female">
              <label for="female">Female</label>
            </div>
          </div>
          <div class="mb-3">
            <label for="hospital" class="form-label">Hospital</label>
            <input type="text" class="form-control" id="hospital" name="hospital" >
          </div>
          <div class="mb-3">
            <label for="attending_physician" class="form-label">Attending Physician</label>
            <input type="text" class="form-control" id="attending_physician" name="attending_physician" >
          </div>
          <div class="mb-3">
            <label class="form-label">Ward</label>
            <div>
              <input type="radio" id="pay" name="ward" value="Pay" >
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
        <input type="date" class="form-control" id="when" name="when"  >
    </div>
          <div class="mb-3">
            <label for="where" class="form-label">Where</label>
            <input type="text" class="form-control" id="where" name="where">
          </div>
        </div><br><br><br>








                <!-- Request Type Dropdown -->
                <label for="requestType">Request Type:</label><br>
                <select id="requestType" name="requestType" onchange="showUnits()">
                    <option value="wholeBlood">Whole Blood</option>
                    <option value="packedRBC">Packed RBC</option>
                    <option value="washedRBC">Washed RBC</option> <!-- New Option -->
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
    <div id="wholeBloodUnits" >
        <label for="wholeBloodUnitsInput">Units of Whole Blood:</label>
        <input type="number" id="wholeBloodUnitsInput" name="wholeBloodUnitsInput" placeholder="Enter units" min="1"><br><br>

        <label style="display: block;">
            <input type="radio" id="WB1" name="wholeBloodType" value="WB1"> WB1
        </label>
        <label style="display: block;">
            <input type="radio" id="WB2" name="wholeBloodType" value="WB2"> WB2
        </label>
        
        <!-- Reason text box (Initially hidden) -->
        <div id="reasonDiv" style="display:none;">
            <label for="reasonText">Please provide the reason for selecting WB2:</label><br>
            <textarea id="reasonText" name="reasonText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
        </div>
    </div>

    <div id="packedRBCUnits" style="display:none;">
        <label for="packedRBCUnitsInput">Units of Packed RBC:</label>
        <input type="number" id="packedRBCUnitsInput" name="packedRBCUnitsInput" placeholder="Enter units" min="1"><br><br>

        <label style="display: block;">
            <input type="radio" id="R1" name="packedRBCType" value="R1"> R1
        </label>
        <label style="display: block;">
            <input type="radio" id="R2" name="packedRBCType" value="R2"> R2
        </label>
        <label style="display: block;">
            <input type="radio" id="R3" name="packedRBCType" value="R3"> R3
        </label>
        <label style="display: block;">
            <input type="radio" id="R4" name="packedRBCType" value="R4"> R4
        </label>
        <label style="display: block;">
            <input type="radio" id="R5" name="packedRBCType" value="R5"> R5
        </label>

        <!-- Reason text box (Initially hidden) -->
        <div id="reasonRBCDiv" style="display:none;">
            <label for="reasonRBCText">Please provide the reason for selecting R5 Packed RBC:</label><br>
            <textarea id="reasonRBCText" name="reasonRBCText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
        </div>
    </div>


    <div id="washedRBCUnits" style="display:none;">
        <label for="washedRBCUnitsInput">Units of Washed RBC:</label>
        <input type="number" id="washedRBCUnitsInput" name="washedRBCUnitsInput" placeholder="Enter units" min="1"><br><br>

        <!-- Add radio buttons for WP1 to WP4 -->
        <label style="display: block;">
            <input type="radio" id="WashedWP1" name="washedRBCType" value="WP1"> WP1
        </label>
        <label style="display: block;">
            <input type="radio" id="WashedWP2" name="washedRBCType" value="WP2"> WP2
        </label>
        <label style="display: block;">
            <input type="radio" id="WashedWP3" name="washedRBCType" value="WP3"> WP3
        </label>
        <label style="display: block;">
            <input type="radio" id="WashedWP4" name="washedRBCType" value="WP4"> WP4
        </label>

        <!-- Reason text box (Initially hidden) -->
        <div id="reasonWashedRBCDiv" style="display:none;">
            <label for="reasonWashedRBCText">Please provide the reason for selecting WP4 Washed RBC:</label><br>
            <textarea id="reasonWashedRBCText" name="reasonWashedRBCText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
        </div>
    </div>

    <!-- Buffy Coat-Poor RBC Section -->
    <div id="buffyCoatPoorRBCUnits" style="display:none;">
        <label for="buffyCoatPoorRBCUnitsInput">Units of Buffy Coat-Poor RBC:</label>
        <input type="number" id="buffyCoatPoorRBCUnitsInput" name="buffyCoatPoorRBCUnitsInput" placeholder="Enter units" min="1"><br><br>

        <!-- Add radio buttons for WP1 to WP4 -->
        <label style="display: block;">
            <input type="radio" id="BuffyWP1" name="buffyRBCType" value="WP1"> WP1
        </label>
        <label style="display: block;">
            <input type="radio" id="BuffyWP2" name="buffyRBCType" value="WP2"> WP2
        </label>
        <label style="display: block;">
            <input type="radio" id="BuffyWP3" name="buffyRBCType" value="WP3"> WP3
        </label>
        <label style="display: block;">
            <input type="radio" id="BuffyWP4" name="buffyRBCType" value="WP4"> WP4
        </label>

        <!-- Reason text box (Initially hidden) -->
        <div id="reasonBuffyRBCDiv" style="display:none;">
            <label for="reasonBuffyRBCText">Please provide the reason for selecting WP4 Buffy Coat-Poor RBC:</label><br>
            <textarea id="reasonBuffyRBCText" name="reasonBuffyRBCText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
        </div>
    </div>


 <!-- Platelet Concentrate Section -->
<div id="plateletConcentrateUnits" style="display:none;">
    <label for="plateletConcentrateUnitsInput">Units of Platelet Concentrate:</label>
    <input type="number" id="plateletConcentrateUnitsInput" name="plateletConcentrateUnitsInput" placeholder="Enter units" min="1"><br><br>

    <!-- Radio buttons for Platelet Concentrate -->
    <label style="display: block;">
        <input type="radio" id="P1" name="plateletConcentrateType" value="P1"> P1
    </label>
    <label style="display: block;">
        <input type="radio" id="P2" name="plateletConcentrateType" value="P2"> P2
    </label>
    <label style="display: block;">
        <input type="radio" id="P3" name="plateletConcentrateType" value="P3"> P3
    </label>
    <label style="display: block;">
        <input type="radio" id="P4" name="plateletConcentrateType" value="P4"> P4
    </label>
    <label style="display: block;">
        <input type="radio" id="P5" name="plateletConcentrateType" value="P5"> P5
    </label>
    <label style="display: block;">
        <input type="radio" id="P6" name="plateletConcentrateType" value="P6"> P6
    </label>

    <!-- Reason text box (Initially hidden) -->
    <div id="reasonPlateletConcentrateDiv" style="display:none;">
        <label for="reasonPlateletConcentrateText">Please provide the reason for selecting P6 Platelet Concentrate:</label><br>
        <textarea id="reasonPlateletConcentrateText" name="reasonPlateletConcentrateText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
    </div>
</div>

<!-- Apheresis Platelets Units -->
<div id="apheresisPlateletsUnits" style="display:none;">
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
        P1
    </label>
    <label style="display: block;">
        <input type="radio" id="P2" name="apheresisPlateletType" value="P2">
        P2
    </label>
    <label style="display: block;">
        <input type="radio" id="P3" name="apheresisPlateletType" value="P3">
        P3
    </label>
    <label style="display: block;">
        <input type="radio" id="P4" name="apheresisPlateletType" value="P4">
        P4
    </label>
    <label style="display: block;">
        <input type="radio" id="P5" name="apheresisPlateletType" value="P5">
        P5
    </label>
    <label style="display: block;">
        <input type="radio" id="P6" name="apheresisPlateletType" value="P6">
        P6
    </label>

    <!-- Reason text box for P6 (Initially hidden) -->
    <div id="reasonApheresisPlateletsDiv" style="display:none;">
        <label for="reasonApheresisPlateletsText">Please provide the reason for selecting P6 Apheresis Platelets:</label><br>
        <textarea id="reasonApheresisPlateletsText" name="reasonApheresisPlateletsText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
    </div>
</div>

<!-- Leukocyte-Poor Platelets Units -->
<!-- Leukocyte Poor Platelets Units -->
<div id="leukocytePoorPlateletsUnits" style="display:none;">
    <label for="leukocytePoorPlateletsUnitsInput">Units of Leukocyte-Poor Platelets:</label>
    <input type="number" id="leukocytePoorPlateletsUnitsInput" name="leukocytePoorPlateletsUnitsInput" placeholder="Enter units" min="1"><br><br>

    <!-- Radio buttons for Leukocyte-Poor Platelets Types -->
    <label style="display: block;">
        <input type="radio" id="P1" name="leukocytePoorPlateletType" value="P1">
        P1
    </label>
    <label style="display: block;">
        <input type="radio" id="P2" name="leukocytePoorPlateletType" value="P2">
        P2
    </label>
    <label style="display: block;">
        <input type="radio" id="P3" name="leukocytePoorPlateletType" value="P3">
        P3
    </label>
    <label style="display: block;">
        <input type="radio" id="P4" name="leukocytePoorPlateletType" value="P4">
        P4
    </label>
    <label style="display: block;">
        <input type="radio" id="P5" name="leukocytePoorPlateletType" value="P5">
        P5
    </label>
    <label style="display: block;">
        <input type="radio" id="P6" name="leukocytePoorPlateletType" value="P6">
        P6
    </label>

    <!-- Reason text box for P6 (Initially hidden) -->
    <div id="reasonLeukocytePlateletsDiv" style="display:none;">
        <label for="reasonLeukocytePlateletsText">Please provide the reason for selecting P6 Leukocyte-Poor Platelets:</label><br>
        <textarea id="reasonLeukocytePlateletsText" name="reasonLeukocytePlateletsText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
    </div>
</div>

                <!-- Fresh Frozen Plasma Units -->
<!-- Fresh Frozen Plasma Units -->
<div id="freshFrozenPlasmaUnits" style="display:none;">
    <label for="freshFrozenPlasmaUnitsInput">Units of Fresh Frozen Plasma:</label>
    <input type="number" id="freshFrozenPlasmaUnitsInput" name="freshFrozenPlasmaUnitsInput" placeholder="Enter units" min="1"><br><br>

    <!-- Radio buttons for Fresh Frozen Plasma Types -->
    <label style="display: block;">
        <input type="radio" id="F1" name="freshFrozenPlasmaType" value="F1">
        F1
    </label>
    <label style="display: block;">
        <input type="radio" id="F2" name="freshFrozenPlasmaType" value="F2">
        F2
    </label>
    <label style="display: block;">
        <input type="radio" id="F3" name="freshFrozenPlasmaType" value="F3">
        F3
    </label>
    <label style="display: block;">
        <input type="radio" id="F4" name="freshFrozenPlasmaType" value="F4">
        F4
    </label>
    <label style="display: block;">
        <input type="radio" id="F5" name="freshFrozenPlasmaType" value="F5">
        F5
    </label>

    <!-- Reason text box for F5 (Initially hidden) -->
    <div id="reasonFreshFrozenPlasmaDiv" style="display:none;">
        <label for="reasonFreshFrozenPlasmaText">Please provide the reason for selecting F5 Fresh Frozen Plasma:</label><br>
        <textarea id="reasonFreshFrozenPlasmaText" name="reasonFreshFrozenPlasmaText" placeholder="Enter reason" rows="4" cols="50"></textarea><br><br>
    </div>
</div>

<!-- Leukocyte-Poor Fresh Frozen Plasma Units -->
<div id="leukocytePoorFreshFrozenPlasmaUnits" style="display:none;">
    <label for="leukocytePoorFreshFrozenPlasmaUnitsInput">Units of Leukocyte-Poor Fresh Frozen Plasma:</label>
    <input type="number" id="leukocytePoorFreshFrozenPlasmaUnitsInput" name="leukocytePoorFreshFrozenPlasmaUnitsInput" placeholder="Enter units" min="1"><br><br>

    <!-- Radio buttons for Leukocyte-Poor Fresh Frozen Plasma -->
    <label style="display: block;">
        <input type="radio" id="F1" name="leukocytePoorFreshFrozenPlasmaType" value="F1">
        F1
    </label>
    <label style="display: block;">
        <input type="radio" id="F2" name="leukocytePoorFreshFrozenPlasmaType" value="F2">
        F2
    </label>
    <label style="display: block;">
        <input type="radio" id="F3" name="leukocytePoorFreshFrozenPlasmaType" value="F3">
        F3
    </label>
    <label style="display: block;">
        <input type="radio" id="F4" name="leukocytePoorFreshFrozenPlasmaType" value="F4">
        F4
    </label>
    <label style="display: block;">
        <input type="radio" id="F5" name="leukocytePoorFreshFrozenPlasmaType" value="F5" onclick="displayReasonBox()">
        F5
    </label>

    <!-- Reason Textbox for F5 -->
    <div id="reasonleukocytePoorFreshFrozenPlasmaDiv" style="display:none;">
        <label for="reasonleukocytePoorFreshFrozenPlasmaText">Please provide a reason:</label><br>
        <textarea id="reasonleukocytePoorFreshFrozenPlasmaDText" name="reasonleukocytePoorFreshFrozenPlasmaDText" rows="4" cols="50" placeholder="Enter reason here"></textarea><br><br>
    </div>
</div>

                <!-- Cryoprecipitate Units -->
<div id="cryoprecipitateUnits" style="display:none;">
    <label for="cryoprecipitateUnitsInput">Units of Cryoprecipitate:</label>
    <input type="number" id="cryoprecipitateUnitsInput" name="cryoprecipitateUnitsInput" placeholder="Enter units" min="1"><br><br>

    <!-- Radio buttons for Cryoprecipitate -->
    <label style="display: block;">
        <input type="radio" id="C1" name="cryoprecipitateType" value="C1">
        C1
    </label>
    <label style="display: block;">
        <input type="radio" id="C2" name="cryoprecipitateType" value="C2">
        C2
    </label>
    <label style="display: block;">
        <input type="radio" id="C3" name="cryoprecipitateType" value="C3">
        C3
    </label>
    <label style="display: block;">
        <input type="radio" id="C4" name="cryoprecipitateType" value="C4">
        C4
    </label>
    <div id="reasonForC4" style="display:none;">
        <label for="reasoncryoprecipitateText">Please provide a reason:</label><br>
        <textarea id="reasoncryoprecipitateText" name="reasoncryoprecipitateText" rows="4" cols="50" placeholder="Enter reason here"></textarea><br><br>
    </div>
    </div>


            <button type="submit">Submit Request</button>
    </form>

        </div>
    </div>

    <div id="bloodRequestsContainer"></div>

    <div id="reportContainer"></div>
    </script>
    <div id="contentContainer"></div>


<div id="statisticsContainer"></div>

<script src="js/hospital.js"></script>
<script>
    function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            sidebar.classList.toggle('open');
            content.classList.toggle('shifted');
        }

function loadReport() {
    document.getElementById('bloodRequestsContainer').style.display = 'none';
    document.getElementById('reportContainer').style.display = 'block';

    fetch('report.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('reportContainer').innerHTML = `
                <div class="report-card">
                   
                    <div class="card-body">${data}</div>
                </div>
            `;
        })
        .catch(error => console.error('Error loading report:', error));
}

function loadContent(contentType) {
    if (contentType === 'bloodRequests') {
        document.getElementById('reportContainer').style.display = 'none';
        document.getElementById('bloodRequestsContainer').style.display = 'block';

        fetch('process_history_request.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('bloodRequestsContainer').innerHTML = data;
            })
            .catch(error => console.error('Error fetching data:', error));
    }
}

function closeContent() {
    document.getElementById('reportContainer').style.display = 'none';
    document.getElementById('bloodRequestsContainer').style.display = 'none';
}

</script>

</script>
</body>
</html>