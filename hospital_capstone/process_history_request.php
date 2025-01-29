<?php
// Include the connection.php file for database connection
include('connection.php');

// SQL query to fetch the specific columns from the blood_request table
$sql = "SELECT 
            surname, 
            firstname, 
            middlename, 
            age, 
            birthdate, 
            sex, 
            hospital, 
            attending_physician, 
            ward, 
            room_no, 
            tel_no, 
            clinical_diagnosis, 
            `when`, 
            `where`,
            whole_blood_units,
            W,
            R,
            WP,
            P,
            F,
            C,
            packed_rbc_units,
            washed_rbc_units,
            buffy_coat_poor_rbc_units,
            platelet_concentrate_units,
            apheresis_platelets_units,
            leukocyte_poor_platelet_concentrate_units,
            fresh_frozen_plasma_units,
            leukocyte_poor_fresh_frozen_plasma_units,
            cryoprecipitate_units,
            hospital_id,
            WB_reasons,
            R_reasons,
            WP_reasons,
            P_reasons,
            F_reasons,
            C_reasons
        FROM blood_request";

$result = $conn->query($sql);

// Check if there are any records to display
if ($result->num_rows > 0) {
    // Start the container for the cards
    echo "<div class='card-container'>";
    
    // Fetch the data and display each row in a card
    while ($row = $result->fetch_assoc()) {
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h3>" . $row['surname'] . ", " . $row['firstname'] . " " . $row['middlename'] . "</h3>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<p><strong>Age:</strong> " . $row['age'] . "</p>";
        echo "<p><strong>Birthdate:</strong> " . $row['birthdate'] . "</p>";
        echo "<p><strong>Sex:</strong> " . $row['sex'] . "</p>";
        echo "<p><strong>Hospital:</strong> " . $row['hospital'] . "</p>";
        echo "<p><strong>Attending Physician:</strong> " . $row['attending_physician'] . "</p>";
        echo "<p><strong>Ward:</strong> " . $row['ward'] . "</p>";
        echo "<p><strong>Room No:</strong> " . $row['room_no'] . "</p>";
        echo "<p><strong>Tel No:</strong> " . $row['tel_no'] . "</p>";
        echo "<p><strong>Clinical Diagnosis:</strong> " . $row['clinical_diagnosis'] . "</p>";
        echo "<p><strong>When:</strong> " . $row['when'] . "</p>";
        echo "<p><strong>Where:</strong> " . $row['where'] . "</p>";

        // Display blood unit information if values are not 0 or NULL
        if ($row['whole_blood_units'] != 0 && $row['whole_blood_units'] != NULL) {
            echo "<p><strong>Whole Blood Units:</strong> " . $row['whole_blood_units'] . "</p>";
        }
        if ($row['packed_rbc_units'] != 0 && $row['packed_rbc_units'] != NULL) {
            echo "<p><strong>Packed RBC Units:</strong> " . $row['packed_rbc_units'] . "</p>";
        }
        if ($row['washed_rbc_units'] != 0 && $row['washed_rbc_units'] != NULL) {
            echo "<p><strong>Washed RBC Units:</strong> " . $row['washed_rbc_units'] . "</p>";
        }
        if ($row['buffy_coat_poor_rbc_units'] != 0 && $row['buffy_coat_poor_rbc_units'] != NULL) {
            echo "<p><strong>Buffy Coat Poor RBC Units:</strong> " . $row['buffy_coat_poor_rbc_units'] . "</p>";
        }
        if ($row['platelet_concentrate_units'] != 0 && $row['platelet_concentrate_units'] != NULL) {
            echo "<p><strong>Platelet Concentrate Units:</strong> " . $row['platelet_concentrate_units'] . "</p>";
        }
        if ($row['apheresis_platelets_units'] != 0 && $row['apheresis_platelets_units'] != NULL) {
            echo "<p><strong>Apheresis Platelets Units:</strong> " . $row['apheresis_platelets_units'] . "</p>";
        }
        if ($row['leukocyte_poor_platelet_concentrate_units'] != 0 && $row['leukocyte_poor_platelet_concentrate_units'] != NULL) {
            echo "<p><strong>Leukocyte Poor Platelet Concentrate Units:</strong> " . $row['leukocyte_poor_platelet_concentrate_units'] . "</p>";
        }
        if ($row['fresh_frozen_plasma_units'] != 0 && $row['fresh_frozen_plasma_units'] != NULL) {
            echo "<p><strong>Fresh Frozen Plasma Units:</strong> " . $row['fresh_frozen_plasma_units'] . "</p>";
        }
        if ($row['leukocyte_poor_fresh_frozen_plasma_units'] != 0 && $row['leukocyte_poor_fresh_frozen_plasma_units'] != NULL) {
            echo "<p><strong>Leukocyte Poor Fresh Frozen Plasma Units:</strong> " . $row['leukocyte_poor_fresh_frozen_plasma_units'] . "</p>";
        }
        if ($row['cryoprecipitate_units'] != 0 && $row['cryoprecipitate_units'] != NULL) {
            echo "<p><strong>Cryoprecipitate Units:</strong> " . $row['cryoprecipitate_units'] . "</p>";
        }

        // Display reasons if values are not 0 or NULL
        if ($row['W'] != 0 && $row['W'] != NULL) {
            echo "<p><strong>WB Reasons:</strong> " . $row['W'] . "</p>";
        }
        if ($row['R'] != 0 && $row['R'] != NULL) {
            echo "<p><strong>WB Reasons:</strong> " . $row['R'] . "</p>";
        }
        if ($row['WP'] != 0 && $row['WP'] != NULL) {
            echo "<p><strong>WB Reasons:</strong> " . $row['WP'] . "</p>";
        }
        if ($row['P'] != 0 && $row['P'] != NULL) {
            echo "<p><strong>WB Reasons:</strong> " . $row['P'] . "</p>";
        }
        if ($row['F'] != 0 && $row['F'] != NULL) {
            echo "<p><strong>WB Reasons:</strong> " . $row['WP'] . "</p>";
        }
        if ($row['C'] != 0 && $row['C'] != NULL) {
            echo "<p><strong>WB Reasons:</strong> " . $row['C'] . "</p>";
        }



        // Display reasons if values are not 0 or NULL
        if ($row['WB_reasons'] != 0 && $row['WB_reasons'] != NULL) {
            echo "<p><strong>WB Reasons:</strong> " . $row['WB_reasons'] . "</p>";
        }
        if ($row['R_reasons'] != 0 && $row['R_reasons'] != NULL) {
            echo "<p><strong>R Reasons:</strong> " . $row['R_reasons'] . "</p>";
        }
        if ($row['WP_reasons'] != 0 && $row['WP_reasons'] != NULL) {
            echo "<p><strong>WP Reasons:</strong> " . $row['WP_reasons'] . "</p>";
        }
        if ($row['P_reasons'] != 0 && $row['P_reasons'] != NULL) {
            echo "<p><strong>P Reasons:</strong> " . $row['P_reasons'] . "</p>";
        }
        if ($row['F_reasons'] != 0 && $row['F_reasons'] != NULL) {
            echo "<p><strong>F Reasons:</strong> " . $row['F_reasons'] . "</p>";
        }
        if ($row['C_reasons'] != 0 && $row['C_reasons'] != NULL) {
            echo "<p><strong>C Reasons:</strong> " . $row['C_reasons'] . "</p>";
        }
        
        echo "</div>"; // End of card-body
        echo "</div>"; // End of card
    }
    
    echo "</div>"; // End of card-container
} else {
    echo "<p>No records found.</p>";
}

// Close the database connection
$conn->close();
?>
