<?php
// Include the connection.php file for database connection
include('connection.php');

// SQL query to fetch the specific columns from the blood_request table
$sql = "SELECT 
            id,
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
            C_reasons,
            status, 
            rejection_reason
        FROM blood_request 
        WHERE status != 'Received'"; // Exclude requests with 'Received' status


$result = $conn->query($sql);

// Check if there are any records to display
if ($result->num_rows > 0) {
    echo "<div class='card-container'>";
    
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

        // Blood unit information
        $blood_units = [
            "Whole Blood Units" => $row['whole_blood_units'],
            "Packed RBC Units" => $row['packed_rbc_units'],
            "Washed RBC Units" => $row['washed_rbc_units'],
            "Buffy Coat Poor RBC Units" => $row['buffy_coat_poor_rbc_units'],
            "Platelet Concentrate Units" => $row['platelet_concentrate_units'],
            "Apheresis Platelets Units" => $row['apheresis_platelets_units'],
            "Leukocyte Poor Platelet Concentrate Units" => $row['leukocyte_poor_platelet_concentrate_units'],
            "Fresh Frozen Plasma Units" => $row['fresh_frozen_plasma_units'],
            "Leukocyte Poor Fresh Frozen Plasma Units" => $row['leukocyte_poor_fresh_frozen_plasma_units'],
            "Cryoprecipitate Units" => $row['cryoprecipitate_units']
        ];
        
        foreach ($blood_units as $label => $value) {
            if (!empty($value)) {
                echo "<p><strong>$label:</strong> $value</p>";
            }
        }

        // Reasons
        $reasons = [
            "WB Reasons" => $row['WB_reasons'],
            "R Reasons" => $row['R_reasons'],
            "WP Reasons" => $row['WP_reasons'],
            "P Reasons" => $row['P_reasons'],
            "F Reasons" => $row['F_reasons'],
            "C Reasons" => $row['C_reasons']
        ];

        foreach ($reasons as $label => $value) {
            if (!empty($value)) {
                echo "<p><strong>$label:</strong> $value</p>";
            }
        }

        // Show 'Received' button only if the status is 'Accepted'
        if ($row['status'] == 'Accepted') {
            echo "<form action='mark_received.php' method='POST'>";
            echo "<form action='mark_received.php' method='POST'>";
echo "<input type='hidden' name='id' value='" . $row['id'] . "'>"; // Use 'id' instead of 'hospital_id'
echo "<button type='submit' class='btn btn-success'>Received</button>";
echo "</form>";

        } elseif ($row['status'] == 'Rejected') {
            echo "<p><strong>Rejection Reason:</strong> " . $row['rejection_reason'] . "</p>";
        }

        echo "</div>"; // End of card-body
        echo "</div>"; // End of card
    }
    
    echo "</div>"; // End of card-container
} else {
    echo "<p>No records found.</p>";
}

$conn->close();
?>
