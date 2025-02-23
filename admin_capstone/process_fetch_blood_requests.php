<?php
// Include the connection.php file for database connection
include('connection.php');


// SQL query to fetch the specific columns from the blood_request table
$sql = "SELECT 
            id,
            hospital_id, 
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
            packed_rbc_units,
            washed_rbc_units,
            buffy_coat_poor_rbc_units,
            platelet_concentrate_units,
            apheresis_platelets_units,
            leukocyte_poor_platelet_concentrate_units,
            fresh_frozen_plasma_units,
            leukocyte_poor_fresh_frozen_plasma_units,
            cryoprecipitate_units,
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
    echo "<div class='card-container' id='userManagementCard'>";
    
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
        $blood_units = [
            'Whole Blood Units' => 'whole_blood_units',
            'Packed RBC Units' => 'packed_rbc_units',
            'Washed RBC Units' => 'washed_rbc_units',
            'Buffy Coat Poor RBC Units' => 'buffy_coat_poor_rbc_units',
            'Platelet Concentrate Units' => 'platelet_concentrate_units',
            'Apheresis Platelets Units' => 'apheresis_platelets_units',
            'Leukocyte Poor Platelet Concentrate Units' => 'leukocyte_poor_platelet_concentrate_units',
            'Fresh Frozen Plasma Units' => 'fresh_frozen_plasma_units',
            'Leukocyte Poor Fresh Frozen Plasma Units' => 'leukocyte_poor_fresh_frozen_plasma_units',
            'Cryoprecipitate Units' => 'cryoprecipitate_units'
        ];

        foreach ($blood_units as $label => $field) {
            if ($row[$field] != 0 && $row[$field] != NULL) {
                echo "<p><strong>{$label}:</strong> " . $row[$field] . "</p>";
            }
        }

        // Display reasons if values are not 0 or NULL
        $reasons = [
            'WB Reasons' => 'WB_reasons',
            'R Reasons' => 'R_reasons',
            'WP Reasons' => 'WP_reasons',
            'P Reasons' => 'P_reasons',
            'F Reasons' => 'F_reasons',
            'C Reasons' => 'C_reasons'
        ];

        foreach ($reasons as $label => $field) {
            if ($row[$field] != 0 && $row[$field] != NULL) {
                echo "<p><strong>{$label}:</strong> " . $row[$field] . "</p>";
            }
        }

        // Accept and Reject buttons
        // Accept and Reject buttons
        echo "<form method='POST' action='process_request.php' class='request-actions'>";
        echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
        echo "<button type='submit' name='action' value='accept' class='accept-btn'>Accept</button>";
        echo "<button type='button' class='reject-btn' onclick='showRejectModal(\"" . $row['id'] . "\")'>Reject</button>";
        echo "</form>";
        
        // Rejection Modal
        echo "<div id='rejectModal' class='modal' style='display:none;'>
                <div class='modal-content'>
                    <span class='close' onclick='closeModal()'>&times;</span>
                    <h2>Reject Blood Request</h2>
                    <form method='POST' action='process_request.php'>
                        <input type='hidden' name='id' id='modal_id'>
                        <label for='rejection_reason'>Rejection Reason:</label>
                        <textarea name='rejection_reason' required></textarea>
                        <button type='submit' name='action' value='reject'>Submit</button>
                    </form>
                </div>
            </div>";
        
        echo "<script>
                function showRejectModal(id) {
                    document.getElementById('rejectModal').style.display = 'block';
                    document.getElementById('modal_id').value = id;
                }
                function closeModal() {
                    document.getElementById('rejectModal').style.display = 'none';
                }
            </script>";
        


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
