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
        // Check if this request ID exists in admin_blood_request table
        $check_admin_request = "SELECT COUNT(*) as request_count FROM admin_blood_request WHERE patient_id = ?";
        $stmt = $conn->prepare($check_admin_request);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $admin_result = $stmt->get_result();
        $admin_request_exists = ($admin_result->fetch_assoc()['request_count'] > 0);
        $stmt->close();
        
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h3>" . $row['surname'] . ", " . $row['firstname'] . " " . $row['middlename'] . "</h3>";
        // Add status badge
        $statusClass = '';
        $status = isset($row['status']) ? $row['status'] : 'Pending';
        switch($status) {
            case 'Pending':
                $statusClass = 'status-pending';
                break;
            case 'Accepted':
                $statusClass = 'status-accepted';
                break;
            case 'Rejected':
                $statusClass = 'status-rejected';
                break;
            default:
                $statusClass = 'status-default';
        }
        echo "<span class='status-badge " . $statusClass . "'>" . htmlspecialchars($status) . "</span>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<p><strong>Patient ID:</strong> " . $row['id'] . "</p>";
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
            echo "<input type='hidden' name='id' value='" . $row['id'] . "'>"; // Use 'id' instead of 'hospital_id'
            echo "<button type='submit' class='btn btn-success'>Received</button>";
            echo "</form>";
        } elseif ($row['status'] == 'Rejected') {
            echo "<div class='rejection-reason'>";
            echo "<p><strong>Rejection Reason:</strong></p>";
            echo "<p class='reason-text'>" . htmlspecialchars($row['rejection_reason']) . "</p>";
            echo "</div>";
        }
        
        // Add "View Blood Exchange" button if there's a matching record in admin_blood_request
        if ($admin_request_exists) {
            echo "<form action='view_blood_exchange.php' method='GET' class='mt-2'>";
            echo "<input type='hidden' name='patient_id' value='" . $row['id'] . "'>";
            echo "<button type='submit' class='btn btn-primary'>View Blood Exchange Request</button>";
            echo "</form>";
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

<style>
.mt-2 {
    margin-top: 10px;
}
.btn-primary {
    background-color: #007bff;
    border: 1px solid #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}
.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 15px;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 15px;
    max-width: 100%;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.card-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.card-body {
    padding: 12px 15px;
}

.card-body p {
    margin: 5px 0;
    font-size: 0.9rem;
    line-height: 1.4;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-pending {
    background-color: #ffc107;
    color: #000;
}

.status-accepted {
    background-color: #28a745;
    color: #fff;
}

.status-rejected {
    background-color: #dc3545;
    color: #fff;
}

.status-default {
    background-color: #6c757d;
    color: #fff;
}

.rejection-reason {
    margin-top: 10px;
    padding: 8px;
    background-color: #fff3f3;
    border-left: 3px solid #dc3545;
    border-radius: 4px;
}

.reason-text {
    margin: 3px 0;
    color: #dc3545;
    font-style: italic;
    font-size: 0.85rem;
}

.btn-success {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-success:hover {
    background-color: #218838;
}
</style>
