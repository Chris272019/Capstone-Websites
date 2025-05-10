<?php
session_start();
include('connection.php');

// Check if hospital is logged in
if (!isset($_SESSION['hospital_id'])) {
    die("Access denied. Please log in first.");
}

// Get the hospital_id from session
$hospital_id = $_SESSION['hospital_id'];

// Query to fetch the blood inventory data
$sql = "SELECT 
            id, 
            hospital_id, 
            blood_type, 
            blood_group, 
            collection_date, 
            expiration_date, 
            volume_ml, 
            number_of_bags, 
            status, 
            collected_by, 
            created_at, 
            updated_at, 
            reserved_by_admin 
        FROM hospital_inventory
        WHERE hospital_id = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind the hospital_id parameter
$stmt->bind_param("i", $hospital_id);

// Execute the query
if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

// Get the result
$result = $stmt->get_result();

// Check if query was successful
if ($result === false) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blood Inventory</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- jQuery first, then SweetAlert CSS and JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Debug information for loaded scripts -->
    <script>
        console.log("Head section loaded");
        window.onload = function() {
            console.log("Window loaded");
            console.log("jQuery loaded: " + (typeof jQuery !== 'undefined'));
            console.log("SweetAlert loaded: " + (typeof Swal !== 'undefined'));
        };
    </script>
    <style>
        :root {
            --primary: #ef4444;
            --primary-light: #fecaca;
            --secondary: #3b82f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        * {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--gray-100);
            color: var(--gray-800);
            padding: 20px;
        }

        h2 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            position: relative;
            display: inline-block;
        }

        h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 60px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }

        .table-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid var(--gray-200);
            margin-bottom: 30px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        th:first-child {
            border-top-left-radius: var(--border-radius);
        }

        th:last-child {
            border-top-right-radius: var(--border-radius);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: var(--gray-100);
        }

        .deliver-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .deliver-btn:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.15);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .status-available {
            background-color: var(--success);
            color: white;
        }

        .status-reserved {
            background-color: var(--warning);
            color: white;
        }

        .status-expired {
            background-color: var(--danger);
            color: white;
        }

        .success-message {
            background-color: var(--success);
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; visibility: hidden; }
        }
        
        /* Custom SweetAlert Styles */
        body.swal2-shown > [aria-hidden="true"] {
            transition: 0.1s filter;
            filter: blur(2px);
        }
        
        .custom-swal-popup {
            border-radius: 12px !important;
            background-color: white !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            padding: 24px !important;
        }
        
        .custom-swal-confirm {
            background-color: var(--secondary) !important;
            color: white !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            padding: 12px 24px !important;
            margin-right: 10px !important;
            transition: all 0.2s ease !important;
        }
        
        .custom-swal-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.2) !important;
        }
        
        .custom-swal-cancel {
            background-color: var(--gray-400) !important;
            color: white !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            padding: 12px 24px !important;
            transition: all 0.2s ease !important;
        }
        
        .custom-swal-cancel:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(148, 163, 184, 0.2) !important;
        }
        
        .swal2-input, .swal2-select {
            border: 1px solid var(--gray-300) !important;
            border-radius: 8px !important;
            padding: 12px 14px !important;
            margin-top: 5px !important;
            background-color: var(--gray-100) !important;
            font-family: 'Inter', 'Segoe UI', sans-serif !important;
            transition: all 0.2s ease !important;
        }
        
        .swal2-input:focus {
            border-color: var(--secondary) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        /* Add these styles after other styles */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px;
        }

        .inventory-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 8px;
            padding: 8px 15px;
            width: 300px;
            border: 1px solid var(--gray-300);
            transition: all 0.2s ease;
        }

        .search-box:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .search-box input {
            border: none;
            background: transparent;
            padding: 8px;
            flex: 1;
            outline: none;
            font-size: 14px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: var(--gray-800);
        }

        .search-box i {
            color: var(--gray-500);
        }

        .filter-container {
            display: flex;
            gap: 10px;
        }

        .filter-dropdown {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--gray-300);
            background-color: white;
            font-size: 14px;
            color: var(--gray-700);
            outline: none;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            transition: all 0.2s ease;
        }

        .filter-dropdown:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .blood-type-badge {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .blood-group-icon {
            width: 32px;
            height: 32px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .inventory-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
            
            .filter-container {
                width: 100%;
                justify-content: space-between;
            }
            
            .filter-dropdown {
                flex: 1;
            }
            
            th, td {
                padding: 12px 10px;
                font-size: 13px;
            }
            
            .blood-group-icon {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
            
            .deliver-btn {
                padding: 8px 12px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Blood Inventory</h2>

    <?php
    // Display success message if redirected from deliver_blood.php
    if (isset($_GET['success']) && $_GET['success'] == '1') {
        echo '<div class="success-message"><i class="fas fa-check-circle"></i> Blood delivery processed successfully!</div>';
    }
    ?>

    <div class="inventory-controls">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="inventorySearch" placeholder="Search inventory...">
        </div>
        
        <div class="filter-container">
            <select class="filter-dropdown" id="statusFilter">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="reserved">Reserved</option>
                <option value="expired">Expired</option>
            </select>
            
            <select class="filter-dropdown" id="bloodTypeFilter">
                <option value="">All Blood Types</option>
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
    </div>

    <div class="table-container">
        <table id="inventoryTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Blood Type</th>
                    <th>Blood Group</th>
                    <th>Collection Date</th>
                    <th>Expiration Date</th>
                    <th>Volume (ml)</th>
                    <th>No. of Bags</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Determine status badge class
                        $statusClass = '';
                        switch(strtolower($row['status'])) {
                            case 'available':
                                $statusClass = 'status-available';
                                break;
                            case 'reserved':
                                $statusClass = 'status-reserved';
                                break;
                            case 'expired':
                                $statusClass = 'status-expired';
                                break;
                            default:
                                $statusClass = '';
                        }
                        
                        // Format dates
                        $collectionDate = date('M d, Y', strtotime($row['collection_date']));
                        $expirationDate = date('M d, Y', strtotime($row['expiration_date']));
                        
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>
                                    <div class='blood-type-badge'>
                                        <div class='blood-group-icon'>{$row['blood_type']}</div>
                                    </div>
                                </td>
                                <td>{$row['blood_group']}</td>
                                <td>{$collectionDate}</td>
                                <td>{$expirationDate}</td>
                                <td>{$row['volume_ml']}</td>
                                <td>{$row['number_of_bags']}</td>
                                <td><span class='status-badge {$statusClass}'>{$row['status']}</span></td>
                                <td>
                                    <button class='deliver-btn' data-id='{$row['id']}' data-volume='{$row['volume_ml']}'>
                                        <i class='fas fa-exchange-alt'></i> Deliver
                                    </button>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align: center; padding: 30px;'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Enable search functionality
document.getElementById('inventorySearch').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById('inventoryTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let rowVisible = false;
        const cells = rows[i].getElementsByTagName('td');
        
        // Skip if no cells (like in header)
        if (cells.length === 0) continue;
        
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(searchTerm) > -1) {
                rowVisible = true;
                break;
            }
        }
        
        rows[i].style.display = rowVisible ? '' : 'none';
    }
});

// Enable status filtering
document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

// Enable blood type filtering
document.getElementById('bloodTypeFilter').addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const bloodTypeFilter = document.getElementById('bloodTypeFilter').value;
    const table = document.getElementById('inventoryTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let showRow = true;
        const cells = rows[i].getElementsByTagName('td');
        
        // Skip if row has no cells (like header or summary rows)
        if (cells.length === 0) continue;
        
        // Status filtering (column 7)
        if (statusFilter && cells[7].textContent.toLowerCase().indexOf(statusFilter) === -1) {
            showRow = false;
        }
        
        // Blood type filtering (column 1)
        if (bloodTypeFilter && !cells[1].textContent.includes(bloodTypeFilter)) {
            showRow = false;
        }
        
        rows[i].style.display = showRow ? '' : 'none';
    }
}

// Test script for debugging
console.log('Test script loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired');
    
    // Test button click
    const testButtons = document.querySelectorAll('.deliver-btn');
    console.log('Found deliver buttons:', testButtons.length);
    
    // Test SweetAlert
    console.log('SweetAlert loaded:', typeof Swal !== 'undefined');
    
    // Check if SweetAlert is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert is not loaded. Please check the script import.');
    }
    
    // Add event listeners to all deliver buttons using event delegation
    document.addEventListener('click', function(event) {
        console.log('Click event:', event.target);
        if (event.target && (event.target.classList.contains('deliver-btn') || event.target.parentElement.classList.contains('deliver-btn'))) {
            console.log('Deliver button clicked');
            // Get the button element (could be the icon or the button itself)
            const button = event.target.classList.contains('deliver-btn') ? event.target : event.target.parentElement;
            const inventoryId = button.dataset.id;
            const currentVolume = button.dataset.volume;
            console.log('Button data:', inventoryId, currentVolume);
            showDeliverModal(inventoryId, currentVolume);
        }
    });
});

function showDeliverModal(inventoryId, currentVolume) {
    console.log('showDeliverModal called with ID:', inventoryId, 'Volume:', currentVolume);
    
    try {
        Swal.fire({
            title: 'Deliver Blood',
            html: `
                <div style="text-align: left; margin-bottom: 20px;">
                    <label for="admin-id" style="display: block; margin-bottom: 8px; font-weight: 500; color: #334155; font-size: 14px;">Hospital:</label>
                    <select id="admin-id" class="swal2-input" style="width: 100%;">
                        <option value="ADMIN_7749">ADMIN_7749</option>
                        <option value="ADMIN_8812">ADMIN_8812</option>
                    </select>
                </div>
                <div style="text-align: left; margin-bottom: 20px;">
                    <label for="blood-volume" style="display: block; margin-bottom: 8px; font-weight: 500; color: #334155; font-size: 14px;">Blood Volume (ml):</label>
                    <input type="number" id="blood-volume" class="swal2-input" value="450" placeholder="Enter blood volume" style="width: 100%;" oninput="calculateBags(this.value)">
                    <p style="margin-top: 8px; font-size: 13px; color: #64748b;">Standard blood bag contains 450ml</p>
                </div>
                <div style="text-align: center; background-color: #f1f5f9; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <p style="margin: 0; font-weight: 600; color: #334155;">
                        Calculated Bags: <span id="calculated-bags" style="color: #ef4444;">1 bag</span>
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirm Delivery',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'custom-swal-confirm',
                cancelButton: 'custom-swal-cancel',
                title: 'custom-swal-title',
                container: 'custom-swal-container'
            },
            didOpen: function() {
                // Calculate bags on load to ensure it's initialized correctly
                calculateBags(document.getElementById('blood-volume').value);
            },
            preConfirm: function() {
                var adminId = document.getElementById('admin-id').value;
                var bloodVolume = document.getElementById('blood-volume').value;
                
                if (!adminId) {
                    Swal.showValidationMessage('Hospital is required');
                    return false;
                }
                
                if (!bloodVolume || bloodVolume <= 0) {
                    Swal.showValidationMessage('Valid blood volume is required');
                    return false;
                }
                
                return { adminId: adminId, bloodVolume: bloodVolume, inventoryId: inventoryId };
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
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message || 'Blood delivery processed successfully!',
                                    icon: 'success',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        confirmButton: 'custom-swal-confirm'
                                    }
                                }).then(function() {
                                    // Reload the page to show updated inventory
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'An error occurred during delivery process.',
                                    icon: 'error',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        confirmButton: 'custom-swal-confirm'
                                    }
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An unexpected error occurred during delivery process.',
                                icon: 'error',
                                customClass: {
                                    popup: 'custom-swal-popup',
                                    confirmButton: 'custom-swal-confirm'
                                }
                            });
                            console.error('Error parsing response:', e, response);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Server error: ' + error,
                            icon: 'error',
                            customClass: {
                                popup: 'custom-swal-popup',
                                confirmButton: 'custom-swal-confirm'
                            }
                        });
                        console.error('AJAX error:', xhr, status, error);
                    }
                });
            }
        });
    } catch (e) {
        console.error('Error showing SweetAlert:', e);
        alert('Error initializing delivery modal. Please try again.');
    }
}

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

<?php
$conn->close();
?>
