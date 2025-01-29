<?php
// Include your database connection file
include('connection.php'); // Assuming you have a file for DB connection
session_start(); // Starts the session
if (!isset($_SESSION['hospital_id'])) {
    die("Hospital ID is not set. Please log in again.");
}
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST);
    $hospital_id = isset($_SESSION['hospital_id']) ? $_SESSION['hospital_id'] : null;
    // Sanitize and assign form values for blood types

    $surname = isset($_POST['surname']) ? $_POST['surname'] : null;
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : null;
    $middlename = isset($_POST['middlename']) ? $_POST['middlename'] : null;
    $age = isset($_POST['age']) ? $_POST['age'] : null;
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $sex = isset($_POST['sex']) ? $_POST['sex'] : null;
    $hospital = isset($_POST['hospital']) ? $_POST['hospital'] : null;
    $attending_physician = isset($_POST['attending_physician']) ? $_POST['attending_physician'] : null;
    $ward = isset($_POST['ward']) ? $_POST['ward'] : null;
    $room_no = isset($_POST['room_no']) ? $_POST['room_no'] : null;
    $tel_no = isset($_POST['tel_no']) ? $_POST['tel_no'] : null;
    $clinical_diagnosis = isset($_POST['clinical_diagnosis']) ? $_POST['clinical_diagnosis'] : null;
    $when = isset($_POST['when']) ? $_POST['when'] : null;
    $where = isset($_POST['where']) ? $_POST['where'] : null;



    $wholeBloodUnits = isset($_POST['wholeBloodUnitsInput']) ? $_POST['wholeBloodUnitsInput'] : null;
    $wholeBloodType = isset($_POST['wholeBloodType']) ? $_POST['wholeBloodType'] : null;
    $reasonText = isset($_POST['reasonText']) ? $_POST['reasonText'] : null;

    $packedRBCUnits = isset($_POST['packedRBCUnitsInput']) ? $_POST['packedRBCUnitsInput'] : null;
    $packedRBCType = isset($_POST['packedRBCType']) ? $_POST['packedRBCType'] : null;
    $reasonRBCText = isset($_POST['reasonRBCText']) ? $_POST['reasonRBCText'] : null;

    // New fields for Washed RBC and Buffy Coat-Poor RBC
    $washedRBCUnits = isset($_POST['washedRBCUnitsInput']) ? $_POST['washedRBCUnitsInput'] : null;
    $washedRBCType = isset($_POST['washedRBCType']) ? $_POST['washedRBCType'] : null;
    $reasonWashedRBCText = isset($_POST['reasonWashedRBCText']) ? $_POST['reasonWashedRBCText'] : null;

    $buffyCoatPoorRBCUnits = isset($_POST['buffyCoatPoorRBCUnitsInput']) ? $_POST['buffyCoatPoorRBCUnitsInput'] : null;
    $buffyRBCType = isset($_POST['buffyRBCType']) ? $_POST['buffyRBCType'] : null;
    $reasonBuffyRBCText = isset($_POST['reasonBuffyRBCText']) ? $_POST['reasonBuffyRBCText'] : null;

    // Combine the radio buttons for Washed RBC and Buffy Coat-Poor RBC into a single WP column
    $wpType = $washedRBCType ?: $buffyRBCType; // Take the first non-null value

    // Combine the reasons for both RBC types into WP_reasons
    $wpReasons = '';

    // Add reason for Washed RBC if selected
    if ($washedRBCType == 'WP4' && $reasonWashedRBCText) {
        $wpReasons .= $reasonWashedRBCText;
    }

    // Add reason for Buffy Coat-Poor RBC if selected
    if ($buffyRBCType == 'WP4' && $reasonBuffyRBCText) {
        if ($wpReasons !== '') {
            $wpReasons .= ' / '; // Add separator between the two reasons
        }
        $wpReasons .= $reasonBuffyRBCText;
    }

    // New fields for Platelet Concentrate, Apheresis Platelets, and Leukocyte-Poor Platelets
    $plateletConcentrateUnits = isset($_POST['plateletConcentrateUnitsInput']) ? $_POST['plateletConcentrateUnitsInput'] : null;
    $plateletConcentrateType = isset($_POST['plateletConcentrateType']) ? $_POST['plateletConcentrateType'] : null;
    $reasonPlateletConcentrateText = isset($_POST['reasonPlateletConcentrateText']) ? $_POST['reasonPlateletConcentrateText'] : null;

    $apheresisPlateletsUnits = isset($_POST['apheresisPlateletsUnits']) ? $_POST['apheresisPlateletsUnits'] : null;
    $apheresisPlateletType = isset($_POST['apheresisPlateletType']) ? $_POST['apheresisPlateletType'] : null;
    $reasonApheresisPlateletsText = isset($_POST['reasonApheresisPlateletsText']) ? $_POST['reasonApheresisPlateletsText'] : null;

    $leukocytePoorPlateletsUnits = isset($_POST['leukocytePoorPlateletsUnitsInput']) ? $_POST['leukocytePoorPlateletsUnitsInput'] : null;
    $leukocytePoorPlateletType = isset($_POST['leukocytePoorPlateletType']) ? $_POST['leukocytePoorPlateletType'] : null;
    $reasonLeukocytePlateletsText = isset($_POST['reasonLeukocytePlateletsText']) ? $_POST['reasonLeukocytePlateletsText'] : null;

    // Combine the radio buttons for Platelet Concentrate, Apheresis Platelets, and Leukocyte-Poor Platelets into a single P column
    $pType = $plateletConcentrateType ?: $apheresisPlateletType ?: $leukocytePoorPlateletType; // Take the first non-null value

    // Combine the reasons for Platelet Concentrate, Apheresis Platelets, and Leukocyte-Poor Platelets into P_reasons
    $pReasons = '';

    // Add reason for Platelet Concentrate if selected
    if ($plateletConcentrateType == 'P6' && $reasonPlateletConcentrateText) {
        $pReasons .= $reasonPlateletConcentrateText;
    }

    // Add reason for Apheresis Platelets if selected
    if ($apheresisPlateletType == 'P6' && $reasonApheresisPlateletsText) {
        if ($pReasons !== '') {
            $pReasons .= ' / '; // Add separator between the two reasons
        }
        $pReasons .= $reasonApheresisPlateletsText;
    }

    // Add reason for Leukocyte-Poor Platelets if selected
    if ($leukocytePoorPlateletType == 'P6' && $reasonLeukocytePlateletsText) {
        if ($pReasons !== '') {
            $pReasons .= ' / '; // Add separator between the two reasons
        }
        $pReasons .= $reasonLeukocytePlateletsText;
    }

    // New fields for Fresh Frozen Plasma and Leukocyte-Poor Fresh Frozen Plasma
    $freshFrozenPlasmaUnits = isset($_POST['freshFrozenPlasmaUnitsInput']) ? $_POST['freshFrozenPlasmaUnitsInput'] : null;
    $freshFrozenPlasmaType = isset($_POST['freshFrozenPlasmaType']) ? $_POST['freshFrozenPlasmaType'] : null;
    $reasonFreshFrozenPlasmaText = isset($_POST['reasonFreshFrozenPlasmaText']) ? $_POST['reasonFreshFrozenPlasmaText'] : null;

    $leukocytePoorFreshFrozenPlasmaUnits = isset($_POST['leukocytePoorFreshFrozenPlasmaUnitsInput']) ? $_POST['leukocytePoorFreshFrozenPlasmaUnitsInput'] : null;
    $leukocytePoorFreshFrozenPlasmaType = isset($_POST['leukocytePoorFreshFrozenPlasmaType']) ? $_POST['leukocytePoorFreshFrozenPlasmaType'] : null;
    $reasonLeukocytePoorFreshFrozenPlasmaText = isset($_POST['reasonleukocytePoorFreshFrozenPlasmaDText']) ? $_POST['reasonleukocytePoorFreshFrozenPlasmaDText'] : null;

    // Combine the radio buttons for Fresh Frozen Plasma and Leukocyte-Poor Fresh Frozen Plasma into a single F column
    $fType = $freshFrozenPlasmaType ?: $leukocytePoorFreshFrozenPlasmaType; // Take the first non-null value

    // Combine the reasons for Fresh Frozen Plasma and Leukocyte-Poor Fresh Frozen Plasma into F_reasons
    $fReasons = '';

    // Add reason for Fresh Frozen Plasma if selected
    if ($freshFrozenPlasmaType == 'F5' && $reasonFreshFrozenPlasmaText) {
        $fReasons .= $reasonFreshFrozenPlasmaText;
    }

    // Add reason for Leukocyte-Poor Fresh Frozen Plasma if selected
    if ($leukocytePoorFreshFrozenPlasmaType == 'F5' && $reasonLeukocytePoorFreshFrozenPlasmaText) {
        if ($fReasons !== '') {
            $fReasons .= ' / '; // Add separator between the two reasons
        }
        $fReasons .= $reasonLeukocytePoorFreshFrozenPlasmaText;
    }
    $cryoprecipitateUnits = isset($_POST['cryoprecipitateUnitsInput']) ? $_POST['cryoprecipitateUnitsInput'] : null;
    $cryoprecipitateType = isset($_POST['cryoprecipitateType']) ? $_POST['cryoprecipitateType'] : null;
    $reasonCryoprecipitateText = isset($_POST['reasoncryoprecipitateText']) ? $_POST['reasoncryoprecipitateText'] : null;



    // Prepare the SQL insert statement
    // Prepare the SQL insert statement
$sql = "INSERT INTO blood_request 
(hospital_id, surname, firstname, middlename, age, birthdate, sex, hospital, attending_physician, ward, room_no, tel_no, clinical_diagnosis, `when`, `where`, 
whole_blood_units, W, WB_reasons, 
packed_rbc_units, R, R_reasons,
washed_rbc_units, WP, WP_reasons, buffy_coat_poor_rbc_units, 
platelet_concentrate_units, apheresis_platelets_units, leukocyte_poor_platelet_concentrate_units, P, P_reasons,
fresh_frozen_plasma_units, leukocyte_poor_fresh_frozen_plasma_units, F, F_reasons,
cryoprecipitate_units, C, C_reasons)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Check if the statement was prepared successfully
if ($stmt === false) {
die("Prepare failed: " . $conn->error);
}

// Bind the parameters
$stmt->bind_param("isssissssssssissssississiiiissiississ", 
$hospital_id, $surname, $firstname, $middlename, $age, $birthdate, $sex, $hospital, 
$attending_physician, $ward, $room_no, $tel_no, $clinical_diagnosis, $when, $where,
$wholeBloodUnits, $wholeBloodType, $reasonText, 
$packedRBCUnits, $packedRBCType, $reasonRBCText,
$washedRBCUnits, $wpType, $wpReasons, $buffyCoatPoorRBCUnits, 
$plateletConcentrateUnits, $apheresisPlateletsUnits, $leukocytePoorPlateletsUnits, $pType, $pReasons, 
$freshFrozenPlasmaUnits, $leukocytePoorFreshFrozenPlasmaUnits, $fType, $fReasons,
$cryoprecipitateUnits, $cryoprecipitateType, $reasonCryoprecipitateText
);

// Execute the statement
if ($stmt->execute()) {
// On success, redirect to hospital dashboard
header("Location: hospital_dashboard.php");
exit;
} else {
echo "Error: " . $stmt->error;
}

// Close the prepared statement
$stmt->close();
}

?>
