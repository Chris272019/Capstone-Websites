<?php
// Include the connection.php file for database connection
include('connection.php');

// Add SweetAlert2 CDN
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

// Handle success and error messages
if (isset($_GET['success'])) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Success!',
                    text: 'Blood exchange request submitted successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Remove 'success' parameter from URL to prevent message on refresh
                    if (window.history.replaceState) {
                        const url = new URL(window.location);
                        url.searchParams.delete('success');
                        window.history.replaceState({}, document.title, url.toString());
                    }
                });
            });
          </script>";
}

if (isset($_GET['error'])) {
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: '" . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
          </script>";
}

// Add this function before the SQL query
function checkBloodAvailability($conn, $blood_type, $blood_group) {
    $sql = "SELECT SUM(volume_ml) as total_volume, SUM(number_of_bags) as total_bags 
            FROM blood_collection_inventory 
            WHERE blood_type = ? AND blood_group = ? AND status = 'Available'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $blood_type, $blood_group);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return [
        'available' => ($row['total_volume'] > 0 && $row['total_bags'] > 0),
        'volume' => $row['total_volume'] ?? 0,
        'bags' => $row['total_bags'] ?? 0
    ];
}

// Initialize blood_types array with all possible combinations
$blood_types = [
    'whole_blood_units' => ['type' => 'Whole Blood', 'group' => ''],
    'packed_rbc_units' => ['type' => 'Packed RBC', 'group' => ''],
    'washed_rbc_units' => ['type' => 'Washed RBC', 'group' => ''],
    'buffy_coat_poor_rbc_units' => ['type' => 'Buffy Coat Poor RBC', 'group' => ''],
    'platelet_concentrate_units' => ['type' => 'Platelet Concentrate', 'group' => ''],
    'apheresis_platelets_units' => ['type' => 'Apheresis Platelets', 'group' => ''],
    'leukocyte_poor_platelet_concentrate_units' => ['type' => 'Leukocyte Poor Platelet Concentrate', 'group' => ''],
    'fresh_frozen_plasma_units' => ['type' => 'Fresh Frozen Plasma', 'group' => ''],
    'leukocyte_poor_fresh_frozen_plasma_units' => ['type' => 'Leukocyte Poor Fresh Frozen Plasma', 'group' => ''],
    'cryoprecipitate_units' => ['type' => 'Cryoprecipitate', 'group' => '']
];

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
            C_reasons,
            W,
            R,
            WP,
            P,
            F,
            C,
            status
        FROM blood_request";

$result = $conn->query($sql);

// Check if there are any records to display
if ($result->num_rows > 0) {
    // Add the buttons for different request statuses
    echo "<div class='status-toggle-container'>";
    echo "<button onclick='showRequests(\"Pending\")' class='blood-toggle-btn' id='pendingBtn'>View Pending Requests</button>";
    echo "<button onclick='showRequests(\"Accepted\")' class='blood-toggle-btn' id='acceptedBtn'>View Accepted Requests</button>";
    echo "<button onclick='showRequests(\"Received\")' class='blood-toggle-btn' id='receivedBtn'>View Received Requests</button>";
    echo "</div>";

    // Add blood-themed CSS
    echo "<style>
    .status-toggle-container {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding: 0 20px;
        justify-content: center;
    }

    .blood-toggle-btn {
        background-color: #8B0000;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 200px;
        font-weight: bold;
    }
    
    .blood-toggle-btn:hover {
        background-color: #A52A2A;
        transform: scale(1.05);
    }

    .blood-toggle-btn.active {
        background-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
    }

    .blood-toggle-btn#pendingBtn {
        background-color: #FFA500;
    }

    .blood-toggle-btn#pendingBtn:hover {
        background-color: #FF8C00;
    }

    .blood-toggle-btn#acceptedBtn {
        background-color: #8B0000;
    }

    .blood-toggle-btn#acceptedBtn:hover {
        background-color: #A52A2A;
    }

    .blood-toggle-btn#receivedBtn {
        background-color: #006400;
    }

    .blood-toggle-btn#receivedBtn:hover {
        background-color: #008000;
    }

    .blood-toggle-btn.active#pendingBtn {
        background-color: #FFD700;
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
    }

    .blood-toggle-btn.active#acceptedBtn {
        background-color: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
    }

    .blood-toggle-btn.active#receivedBtn {
        background-color: #32CD32;
        box-shadow: 0 0 10px rgba(50, 205, 50, 0.5);
    }

    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .card {
        background: linear-gradient(135deg, #fff 0%, #fff5f5 100%);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(139, 0, 0, 0.2);
        overflow: hidden;
        transition: transform 0.3s ease;
        border: 1px solid #ffcccc;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(139, 0, 0, 0.3);
    }

    .card-header {
        background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
        color: white;
        padding: 15px;
        position: relative;
    }

    .card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #ff0000, transparent);
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.2em;
    }

    .card-body {
        padding: 20px;
    }

    .card-body p {
        margin: 8px 0;
        color: #333;
    }

    .card-body strong {
        color: #8B0000;
    }

    .request-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #ffcccc;
    }

    .accept-btn, .reject-btn, .exchange-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: bold;
    }

    .accept-btn {
        background-color: #4CAF50;
        color: white;
    }

    .reject-btn {
        background-color: #f44336;
        color: white;
    }

    .exchange-btn {
        background-color: #8B0000;
        color: white;
    }

    .accept-btn:hover, .reject-btn:hover, .exchange-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .modal {
        background-color: rgba(139, 0, 0, 0.8);
    }

    .modal-content {
        background: linear-gradient(135deg, #fff 0%, #fff5f5 100%);
        border: 2px solid #8B0000;
    }

    .tooltip {
        position: relative;
        display: inline-block;
        margin-top: 5px;
        color: #dc3545;
        font-size: 0.9em;
    }
    
    .accept-btn:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
        opacity: 0.65;
    }
</style>";

    // Start the container for the cards
    echo "<div class='card-container' id='userManagementCard'>";

    // Fetch the data and display each row in a card
    while ($row = $result->fetch_assoc()) {
        echo "<div class='card' id='card-{$row['id']}' data-status='{$row['status']}'>";
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

        // Display the Status if available
        if (!empty($row['status'])) {
            echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . "</p>";
        }

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

        // Display reasons if values are not empty
        $reasons = [
            'WB Reasons' => 'WB_reasons',
            'R Reasons' => 'R_reasons',
            'WP Reasons' => 'WP_reasons',
            'P Reasons' => 'P_reasons',
            'F Reasons' => 'F_reasons',
            'C Reasons' => 'C_reasons'
        ];

        foreach ($reasons as $label => $field) {
            if (!empty($row[$field])) {
                echo "<p><strong>{$label}:</strong> " . htmlspecialchars($row[$field], ENT_QUOTES, 'UTF-8') . "</p>";
            }
        }

        // Modify the Accept and Reject buttons section
        $blood_available = true; // Set to true to enable the button
        $available_types = [];
        $unavailable_types = [];

        // Check each blood type for availability
        foreach ($blood_types as $field => $info) {
            if ($row[$field] > 0 && !empty($info['group'])) {
                $availability = checkBloodAvailability($conn, $info['type'], $info['group']);
                if ($availability['available']) {
                    $available_types[] = [
                        'type' => $info['type'],
                        'group' => $info['group'],
                        'volume' => $availability['volume'],
                        'bags' => $availability['bags']
                    ];
                } else {
                    $unavailable_types[] = [
                        'type' => $info['type'],
                        'group' => $info['group']
                    ];
                }
            }
        }

        // Add data attributes to the form
        $blood_types_json = json_encode($blood_types);
        echo "<form method='POST' action='process_request.php' class='request-actions' data-request-id='{$row['id']}' data-blood-types='{$blood_types_json}'>";
        echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";

        // Enable the button by default
        echo "<button type='submit' name='action' value='accept' class='accept-btn'>Accept</button>";
        echo "<button type='button' class='reject-btn' onclick='showRejectModal(\"" . $row['id'] . "\")'>Reject</button>";
        echo "<button type='button' class='exchange-btn' onclick='showExchangeModal(\"" . $row['id'] . "\", \"" . htmlspecialchars($row['hospital'], ENT_QUOTES, 'UTF-8') . "\")'>Request Blood Exchange</button>";
        echo "</form>";

        // Add tooltip with availability information
        if (!empty($available_types)) {
            $available_types_list = array_map(function($type) {
                return "{$type['type']} {$type['group']} ({$type['bags']} bags)";
            }, $available_types);
            echo "<div class='tooltip' style='color: #28a745;'>Blood available: " . implode(', ', $available_types_list) . "</div>";
        } else {
            $unavailable_types_list = array_map(function($type) {
                return "{$type['type']} {$type['group']}";
            }, $unavailable_types);
            echo "<div class='tooltip' style='color: #dc3545;'>No matching blood available: " . implode(', ', $unavailable_types_list) . "</div>";
        }

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
<script>
    // Update the checkBloodAvailability function
    function checkBloodAvailability(requestId, bloodTypes) {
        console.log('Checking availability for request:', requestId, 'Blood types:', bloodTypes);
        
        $.ajax({
            url: 'check_blood_availability.php',
            type: 'POST',
            data: {
                request_id: requestId,
                blood_types: bloodTypes
            },
            success: function(response) {
                try {
                    console.log('Received response:', response);
                    const data = JSON.parse(response);
                    const card = document.getElementById(`card-${requestId}`);
                    if (!card) {
                        console.error('Card not found for request:', requestId);
                        return;
                    }
                    
                    const acceptBtn = card.querySelector('.accept-btn');
                    const tooltip = card.querySelector('.tooltip');
                    
                    if (!acceptBtn || !tooltip) {
                        console.error('Button or tooltip not found for request:', requestId);
                        return;
                    }
                    
                    // Always enable the button
                    acceptBtn.removeAttribute('disabled');
                    acceptBtn.classList.remove('disabled');
                    acceptBtn.style.backgroundColor = '#4CAF50';
                    acceptBtn.style.cursor = 'pointer';
                    acceptBtn.style.opacity = '1';
                    
                    // Update tooltip with available types
                    if (data.available_types && data.available_types.length > 0) {
                        const availableTypes = data.available_types.map(t => 
                            `${t.type} ${t.group} (${t.bags} bags)`
                        ).join(', ');
                        tooltip.textContent = `Blood available: ${availableTypes}`;
                        tooltip.style.color = '#28a745';
                    } else {
                        const unavailableTypes = data.unavailable_types.map(t => 
                            `${t.type} ${t.group}`
                        ).join(', ');
                        tooltip.textContent = `No matching blood available: ${unavailableTypes}`;
                        tooltip.style.color = '#dc3545';
                    }
                    tooltip.style.display = 'block';
                    
                } catch (error) {
                    console.error('Error parsing response:', error);
                    handleError(requestId, 'Error checking blood availability');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to check blood availability:', error);
                handleError(requestId, 'Error checking blood availability');
            }
        });
    }

    // Add error handling function
    function handleError(requestId, message) {
        const card = document.getElementById(`card-${requestId}`);
        if (card) {
            const acceptBtn = card.querySelector('.accept-btn');
            const tooltip = card.querySelector('.tooltip');
            if (acceptBtn) {
                acceptBtn.setAttribute('disabled', 'disabled');
                acceptBtn.classList.add('disabled');
                acceptBtn.style.backgroundColor = '#6c757d';
                acceptBtn.style.cursor = 'not-allowed';
                acceptBtn.style.opacity = '0.65';
            }
            if (tooltip) {
                tooltip.textContent = message;
                tooltip.style.color = '#dc3545';
                tooltip.style.display = 'block';
            }
        }
    }

    // Add CSS for button states
    const style = document.createElement('style');
    style.textContent = `
        .accept-btn {
            background-color: #4CAF50 !important;
            color: white !important;
            cursor: pointer !important;
            opacity: 1 !important;
            transition: all 0.3s ease;
        }
        .accept-btn:hover {
            background-color: #45a049 !important;
            transform: scale(1.05);
        }
        .tooltip {
            margin-top: 5px;
            font-size: 0.9em;
            display: block;
        }
    `;
    document.head.appendChild(style);

    // Modify the existing showRequests function
    function showRequests(status) {
        // Remove active class from all buttons
        document.querySelectorAll('.blood-toggle-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Add active class to clicked button
        document.getElementById(status.toLowerCase() + 'Btn').classList.add('active');

        // Get all cards
        var cards = document.querySelectorAll('.card');

        // Show/hide cards based on status
        cards.forEach(function(card) {
            if (card.getAttribute('data-status') === status) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Add periodic check for blood availability
    setInterval(function() {
        document.querySelectorAll('.card').forEach(card => {
            if (card.style.display !== 'none') {
                const requestId = card.id.split('-')[1];
                const form = card.querySelector('form');
                if (form) {
                    try {
                        const bloodTypes = JSON.parse(form.getAttribute('data-blood-types'));
                        checkBloodAvailability(requestId, bloodTypes);
                    } catch (error) {
                        console.error('Error parsing blood types:', error);
                    }
                }
            }
        });
    }, 10000); // Check every 10 seconds

    // Show pending requests by default
    document.addEventListener('DOMContentLoaded', function() {
        showRequests('Pending');
    });

    function showRejectModal(id) {
        document.getElementById('rejectModal').style.display = 'block';
        document.getElementById('modal_id').value = id;
    }

    function closeModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function showExchangeModal(id, hospital) {
        // Fetch status from admin_blood_request via AJAX
        $.ajax({
            url: 'fetch_blood_request_status.php',
            type: 'GET',
            dataType: 'json',
            data: { request_id: id },
            success: function(response) {
                let statusText = response.status ? response.status : 'Unknown';

                Swal.fire({
                    title: 'Request Blood Exchange',
                    html: `
                        <form id="bloodExchangeForm" method="POST" action="process_blood_exchange.php">
                            <input type="hidden" name="request_id" value="${id}">
                            <input type="hidden" name="hospital" value="${hospital}">
                            <div class="form-group">
                                <label for="patient_id">Request ID:</label>
                                <input type="text" id="patient_id" class="form-control" value="${id}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="hospital_display">Hospital:</label>
                                <input type="text" id="hospital_display" class="form-control" value="${hospital}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="blood_type">Blood Type:</label>
                                <select name="blood_type" id="blood_type" class="form-control" required>
                                    <option value="">Select Blood Type</option>
                                    <option value="Whole Blood">Whole Blood</option>
                                    <option value="Packed RBC">Packed RBC</option>
                                    <option value="Washed RBC">Washed RBC</option>
                                    <option value="Buffy Coat Poor RBC">Buffy Coat Poor RBC</option>
                                    <option value="Platelet Concentrate">Platelet Concentrate</option>
                                    <option value="Apheresis Platelets">Apheresis Platelets</option>
                                    <option value="Leukocyte Poor Platelet Concentrate">Leukocyte Poor Platelet Concentrate</option>
                                    <option value="Fresh Frozen Plasma">Fresh Frozen Plasma</option>
                                    <option value="Leukocyte Poor Fresh Frozen Plasma">Leukocyte Poor Fresh Frozen Plasma</option>
                                    <option value="Cryoprecipitate">Cryoprecipitate</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="blood_group">Blood Group:</label>
                                <select name="blood_group" id="blood_group" class="form-control" required>
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ml_amount">Amount (ML):</label>
                                <input type="number" name="ml_amount" id="ml_amount" class="form-control" required min="1" step="1" oninput="calculateBags(this.value)">
                                <small>Note: 450ml is considered as 1 bag</small>
                                <div id="bags_calculation" style="margin-top: 5px; color: #666;"></div>
                            </div>
                            <div class="form-group">
                                <label>Status:</label>
                                <p><strong>${statusText}</strong></p>
                            </div>
                            <div class="form-group">
                                <button type="button" id="receivedButton" class="btn btn-success">Mark as Received</button>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const form = document.getElementById('bloodExchangeForm');
                        if (form.checkValidity()) {
                            return form.submit();
                        } else {
                            Swal.showValidationMessage('Please fill in all required fields');
                            return false;
                        }
                    },
                    customClass: {
                        container: 'blood-exchange-modal',
                        popup: 'blood-exchange-popup',
                        content: 'blood-exchange-content',
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-secondary'
                    }
                });

                // Add event listener for Received button
                document.getElementById('receivedButton').addEventListener('click', function() {
                    $.ajax({
                        url: 'update_blood_request_status.php',
                        type: 'POST',
                        data: { request_id: id, status: 'Received' },
                        success: function(response) {
                            Swal.fire({
                                title: 'Success',
                                text: 'Status updated to Received',
                                icon: 'success'
                            }).then(() => {
                                // Close the modal after showing success
                                Swal.close();
                            });
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to update status',
                                icon: 'error'
                            });
                        }
                    });
                });
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to fetch status',
                    icon: 'error'
                });
            }
        });
    }

    // Update the bag calculation function
    function calculateBags(mlAmount) {
        const bagsDiv = document.getElementById('bags_calculation');
        if (!mlAmount || mlAmount <= 0) {
            bagsDiv.innerHTML = '<span style="color: #dc3545;">Please enter a valid amount</span>';
            return;
        }
        
        const bags = Math.floor(mlAmount / 450);
        const remainingMl = mlAmount % 450;
        
        let message = '';
        if (bags > 0) {
            message = `${bags} bag${bags !== 1 ? 's' : ''}`;
            if (remainingMl > 0) {
                message += ` and ${remainingMl}ml`;
            }
        } else {
            message = `${remainingMl}ml`;
        }
        
        bagsDiv.innerHTML = `<span style="color: #28a745;">This equals to ${message}</span>`;
    }
</script>