<?php
// Include database connection
include('connection.php');

// Query to fetch inventory data
$sql = "SELECT 
            i.id,
            u.firstname, 
            u.surname,
            i.blood_type,
            i.collection_date,
            i.expiration_date,
            i.volume_ml,
            i.status,
            i.collected_by,
            i.created_at,
            i.blood_group,
            i.number_of_bags,
            h.hospital_name AS hospital_name
        FROM 
            blood_collection_inventory i
        LEFT JOIN 
            users u ON i.user_id = u.id
        LEFT JOIN
            hospital_accounts h ON i.hospital_id = h.id
        ORDER BY 
            i.collection_date DESC";

$result = mysqli_query($conn, $sql);

$inventory_items = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $inventory_items[] = $row;
    }
}

// Add custom CSS for the table
echo '<style>
    .inventory-table-container {
        margin: 20px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .inventory-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .inventory-table th, .inventory-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .inventory-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    .inventory-table tr:hover {
        background-color: #f5f5f5;
    }
    .search-input {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn-warning {
        padding: 6px 12px;
        background-color: #ffc107;
        color: #000;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-warning:hover {
        background-color: #e0a800;
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875em;
        font-weight: 500;
    }
    .status-available {
        background-color: #28a745;
        color: white;
    }
    .status-reserved {
        background-color: #ffc107;
        color: #000;
    }
    .status-used {
        background-color: #dc3545;
        color: white;
    }
    .status-expired {
        background-color: #6c757d;
        color: white;
    }
    .print-button {
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-bottom: 20px;
    }
    .print-button:hover {
        background-color: #0056b3;
    }
</style>';

// Generate the HTML for displaying inventory information
if (!empty($inventory_items)) {
    echo '<div class="inventory-table-container">
            <h3>Blood Collection Inventory</h3>
            <button type="button" id="printButton" class="print-button">Print</button>
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor Name</th>
                            <th>Blood Type</th>
                            <th>Collection Date</th>
                            <th>Expiration Date</th>
                            <th>Volume (ml)</th>
                            <th>Status</th>
                            <th>Collected By</th>
                            <th>Blood Group</th>
                            <th>Number of Bags</th>
                            <th>Hospital Name</th>
                            <th>Actions</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="searchId" placeholder="Search ID" class="search-input"></th>
                            <th><input type="text" id="searchDonor" placeholder="Search Donor" class="search-input"></th>
                            <th><input type="text" id="searchBloodType" placeholder="Search Blood Type" class="search-input"></th>
                            <th><input type="text" id="searchCollectionDate" placeholder="Search Collection Date" class="search-input"></th>
                            <th><input type="text" id="searchExpirationDate" placeholder="Search Expiration Date" class="search-input"></th>
                            <th><input type="text" id="searchVolume" placeholder="Search Volume" class="search-input"></th>
                            <th><input type="text" id="searchStatus" placeholder="Search Status" class="search-input"></th>
                            <th><input type="text" id="searchCollectedBy" placeholder="Search Collected By" class="search-input"></th>
                            <th><input type="text" id="searchBloodGroup" placeholder="Search Blood Group" class="search-input"></th>
                            <th><input type="text" id="searchNumberOfBags" placeholder="Search Bags" class="search-input"></th>
                            <th><input type="text" id="searchHospitalName" placeholder="Search Hospital" class="search-input"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($inventory_items as $item) {
        // Determine status class
        $statusClass = 'status-' . strtolower($item['status']);
        
        echo '<tr>
                <td>' . htmlspecialchars($item['id']) . '</td>
                <td>' . htmlspecialchars($item['firstname'] . ' ' . $item['surname']) . '</td>
                <td>' . htmlspecialchars($item['blood_type']) . '</td>
                <td>' . date('M d, Y', strtotime($item['collection_date'])) . '</td>
                <td>' . date('M d, Y', strtotime($item['expiration_date'])) . '</td>
                <td>' . htmlspecialchars($item['volume_ml']) . '</td>
                <td><span class="status-badge ' . $statusClass . '">' . htmlspecialchars($item['status']) . '</span></td>
                <td>' . htmlspecialchars($item['collected_by']) . '</td>
                <td>' . htmlspecialchars($item['blood_group'] ? $item['blood_group'] : 'N/A') . '</td>
                <td>' . htmlspecialchars($item['number_of_bags']) . '</td>
                <td>' . htmlspecialchars($item['hospital_name'] ? $item['hospital_name'] : 'N/A') . '</td>
                <td>
                    <button class="btn-warning deliver-inventory" data-id="' . $item['id'] . '">Deliver</button>
                </td>
              </tr>';
    }
    
    echo '</tbody>
        </table>
    </div>
    </div>';
} else {
    echo '<div class="inventory-table-container">
            <h3>Blood Collection Inventory</h3>
            <p>No inventory records available at the moment.</p>
            <p class="text-muted">New blood collections will appear here once they are added to the system.</p>
          </div>';
}

// Close the database connection
mysqli_close($conn);
?>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Add search functionality for each column in first table
    const searchInputs = {
        id: document.getElementById('searchId'),
        donor: document.getElementById('searchDonor'),
        bloodType: document.getElementById('searchBloodType'),
        collectionDate: document.getElementById('searchCollectionDate'),
        expirationDate: document.getElementById('searchExpirationDate'),
        volume: document.getElementById('searchVolume'),
        status: document.getElementById('searchStatus'),
        collectedBy: document.getElementById('searchCollectedBy'),
        hospitalId: document.getElementById('searchHospitalId'),
        bloodGroup: document.getElementById('searchBloodGroup'),
        numberOfBags: document.getElementById('searchNumberOfBags')
    };

    for (let key in searchInputs) {
        if (searchInputs[key]) {
            searchInputs[key].addEventListener('input', function () {
                filterTable('inventoryTable', key, this.value);
            });
        }
    }
    
    // Add search functionality for each column in second table if it exists
    const searchInputs2 = {
        id: document.getElementById('searchId2'),
        donor: document.getElementById('searchDonor2'),
        bloodType: document.getElementById('searchBloodType2'),
        collectionDate: document.getElementById('searchCollectionDate2'),
        expirationDate: document.getElementById('searchExpirationDate2'),
        volume: document.getElementById('searchVolume2'),
        status: document.getElementById('searchStatus2'),
        collectedBy: document.getElementById('searchCollectedBy2'),
        hospitalId: document.getElementById('searchHospitalId2'),
        bloodGroup: document.getElementById('searchBloodGroup2'),
        numberOfBags: document.getElementById('searchNumberOfBags2')
    };

    for (let key in searchInputs2) {
        if (searchInputs2[key]) {
            searchInputs2[key].addEventListener('input', function () {
                filterTable('inventoryTable2', key, this.value);
            });
        }
    }

    function filterTable(tableId, column, value) {
        const rows = document.querySelectorAll(`#${tableId} tbody tr`);
        rows.forEach(row => {
            const cell = row.querySelector(`td:nth-child(${getColumnIndex(column)})`);
            if (cell) {
                row.style.display = cell.textContent.toLowerCase().includes(value.toLowerCase()) ? '' : 'none';
            }
        });
    }

    function getColumnIndex(column) {
        const columnIndexes = {
            id: 1,
            donor: 2,
            bloodType: 3,
            collectionDate: 4,
            expirationDate: 5,
            volume: 6,
            status: 7,
            collectedBy: 8,
            hospitalId: 9,
            bloodGroup: 10,
            numberOfBags: 11
        };
        return columnIndexes[column];
    }

    // Handle inventory delivery button with SweetAlert2
    $(".deliver-inventory").click(function() {
        const inventoryId = $(this).data("id");

        // Fetch hospital names first
        $.ajax({
            url: "fetch_hospitals.php",
            type: "GET",
            success: function(hospitals) {
                const hospitalOptions = hospitals.map(hospital => 
                    `<option value="${hospital.id}">${hospital.name}</option>`
                ).join('');

                Swal.fire({
                    title: "Confirm Delivery",
                    html: `
                        <p>Are you sure you want to mark this item as Reserved?</p>
                        <select id="hospitalSelect" class="form-control mt-3">
                            <option value="">Select Hospital</option>
                            ${hospitalOptions}
                        </select>
                        <div class="form-group mt-3">
                            <label for="bloodUnits">Enter Volume (ml)</label>
                            <input type="number" id="bloodUnits" class="form-control" min="1" required>
                            <small class="text-muted">Standard blood bag contains 450ml</small>
                        </div>
                        <div class="form-group mt-3">
                            <label>Calculated Bags:</label>
                            <div id="bagCalculation" class="alert alert-info">0 bags</div>
                        </div>
                    `,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Confirm Delivery",
                    preConfirm: () => {
                        const hospitalId = document.getElementById('hospitalSelect').value;
                        const bloodUnits = document.getElementById('bloodUnits').value;
                        
                        if (!hospitalId) {
                            Swal.showValidationMessage('Please select a hospital');
                            return false;
                        }
                        if (!bloodUnits || bloodUnits < 1) {
                            Swal.showValidationMessage('Please enter a valid volume of blood');
                            return false;
                        }
                        return { hospitalId, bloodUnits };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Calculate the number of bags based on the entered ml
                        const ml = parseFloat(result.value.bloodUnits);
                        const bags = Math.ceil(ml / 450);
                        const actualMl = bags * 450;

                        $.ajax({
                            url: "process_delivery.php",
                            type: "POST",
                            data: { 
                                id: inventoryId,
                                hospital_id: result.value.hospitalId,
                                blood_units: actualMl,
                                number_of_bags: bags
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Success!",
                                    text: response,
                                    icon: "success",
                                    confirmButtonText: "OK"
                                }).then(() => {
                                    location.reload(); // Reload page to update status
                                });
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: "Error",
                                    text: xhr.responseText || "Error processing the delivery.",
                                    icon: "error"
                                });
                            }
                        });
                    }
                });

                // Add event listener for ml input to calculate bags
                document.getElementById('bloodUnits').addEventListener('input', function() {
                    const ml = parseFloat(this.value) || 0;
                    const bags = Math.ceil(ml / 450); // Calculate bags based on 450ml per bag
                    document.getElementById('bagCalculation').textContent = `${bags} bags (${ml}ml)`;
                });
            },
            error: function() {
                Swal.fire({
                    title: "Error",
                    text: "Error fetching hospital list.",
                    icon: "error"
                });
            }
        });
    });

    // Handle the print button
    $('#printButton').click(function() {
        var printContent = '';
        
        // Get content from first table
        if (document.getElementById('inventoryTable')) {
            printContent += '<h3>Inventory List</h3>';
            printContent += '<table class="table table-striped">' + $('#inventoryTable').html() + '</table>';
        }
        
        // Get content from second table if it exists
        if (document.getElementById('inventoryTable2')) {
            printContent += '<h3>Inventory List (Continued)</h3>';
            printContent += '<table class="table table-striped">' + $('#inventoryTable2').html() + '</table>';
        }
        
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Inventory List</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });
});
</script>