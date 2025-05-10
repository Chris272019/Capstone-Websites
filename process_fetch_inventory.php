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
            i.plasma_type
        FROM 
            blood_collection_inventory i
        LEFT JOIN 
            users u ON i.user_id = u.id
        ORDER BY 
            i.collection_date DESC";

$result = mysqli_query($conn, $sql);

$inventory_items = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $inventory_items[] = $row;
    }
}

// Generate the HTML for displaying inventory information
if (!empty($inventory_items)) {
    // Determine if we need to split the display (for example, if there are more than 10 items)
    $split_display = count($inventory_items) > 10;
    
    if ($split_display) {
        // Start with a container div with flexbox for side-by-side display
        echo '<div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">';
    }
    
    echo '<div class="card">
            <h5 class="card-header">Blood Inventory</h5>
            <div class="card-body">
                <button type="button" id="printButton" class="btn btn-primary mb-3">Print</button>
                <table class="table" id="inventoryTable">
                    <thead>
                        <tr>
                            <th><input type="text" id="searchId" placeholder="Search ID" class="form-control"></th>
                            <th><input type="text" id="searchDonor" placeholder="Search Donor Name" class="form-control"></th>
                            <th><input type="text" id="searchBloodType" placeholder="Search Blood Type" class="form-control"></th>
                            <th><input type="text" id="searchCollectionDate" placeholder="Search Collection Date" class="form-control"></th>
                            <th><input type="text" id="searchExpirationDate" placeholder="Search Expiration Date" class="form-control"></th>
                            <th><input type="text" id="searchVolume" placeholder="Search Volume" class="form-control"></th>
                            <th><input type="text" id="searchStatus" placeholder="Search Status" class="form-control"></th>
                            <th><input type="text" id="searchCollectedBy" placeholder="Search Collected By" class="form-control"></th>
                            <th><input type="text" id="searchPlasmaType" placeholder="Search Plasma Type" class="form-control"></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // If splitting the display, only show the first half in the first table
    $items_to_display = $split_display ? array_slice($inventory_items, 0, ceil(count($inventory_items) / 2)) : $inventory_items;
    
    foreach ($items_to_display as $item) {
        // Determine status class
        $statusClass = '';
        switch ($item['status']) {
            case 'Available':
                $statusClass = 'text-success';
                break;
            case 'Reserved':
                $statusClass = 'text-warning';
                break;
            case 'Used':
                $statusClass = 'text-danger';
                break;
            case 'Expired':
                $statusClass = 'text-secondary';
                break;
            default:
                $statusClass = '';
        }
        
        echo '<tr>
                <td>' . htmlspecialchars($item['id']) . '</td>
                <td>' . htmlspecialchars($item['firstname'] . ' ' . $item['surname']) . '</td>
                <td>' . htmlspecialchars($item['blood_type']) . '</td>
                <td>' . date('M d, Y', strtotime($item['collection_date'])) . '</td>
                <td>' . date('M d, Y', strtotime($item['expiration_date'])) . '</td>
                <td>' . htmlspecialchars($item['volume_ml']) . '</td>
                <td><span class="' . $statusClass . '">' . htmlspecialchars($item['status']) . '</span></td>
                <td>' . htmlspecialchars($item['collected_by']) . '</td>
                <td>' . htmlspecialchars($item['plasma_type'] ? $item['plasma_type'] : 'N/A') . '</td>
                <td>
                    <button class="btn btn-warning btn-sm deliver-inventory" data-id="' . $item['id'] . '">Deliver</button>
                </td>
              </tr>';
    }
    
    echo '</tbody>
        </table>
    </div>
    </div>';
    
    // If splitting the display, create a second table for the second half
    if ($split_display) {
        echo '</div>
              <div class="col-md-6">
              <div class="card">
                <h5 class="card-header">Blood Inventory (Continued)</h5>
                <div class="card-body">
                    <table class="table" id="inventoryTable2">
                        <thead>
                            <tr>
                                <th><input type="text" id="searchId2" placeholder="Search ID" class="form-control"></th>
                                <th><input type="text" id="searchDonor2" placeholder="Search Donor Name" class="form-control"></th>
                                <th><input type="text" id="searchBloodType2" placeholder="Search Blood Type" class="form-control"></th>
                                <th><input type="text" id="searchCollectionDate2" placeholder="Search Collection Date" class="form-control"></th>
                                <th><input type="text" id="searchExpirationDate2" placeholder="Search Expiration Date" class="form-control"></th>
                                <th><input type="text" id="searchVolume2" placeholder="Search Volume" class="form-control"></th>
                                <th><input type="text" id="searchStatus2" placeholder="Search Status" class="form-control"></th>
                                <th><input type="text" id="searchCollectedBy2" placeholder="Search Collected By" class="form-control"></th>
                                <th><input type="text" id="searchPlasmaType2" placeholder="Search Plasma Type" class="form-control"></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        // Display the second half of items
        foreach (array_slice($inventory_items, ceil(count($inventory_items) / 2)) as $item) {
            // Determine status class
            $statusClass = '';
            switch ($item['status']) {
                case 'Available':
                    $statusClass = 'text-success';
                    break;
                case 'Reserved':
                    $statusClass = 'text-warning';
                    break;
                case 'Used':
                    $statusClass = 'text-danger';
                    break;
                case 'Expired':
                    $statusClass = 'text-secondary';
                    break;
                default:
                    $statusClass = '';
            }
            
            echo '<tr>
                    <td>' . htmlspecialchars($item['id']) . '</td>
                    <td>' . htmlspecialchars($item['firstname'] . ' ' . $item['surname']) . '</td>
                    <td>' . htmlspecialchars($item['blood_type']) . '</td>
                    <td>' . date('M d, Y', strtotime($item['collection_date'])) . '</td>
                    <td>' . date('M d, Y', strtotime($item['expiration_date'])) . '</td>
                    <td>' . htmlspecialchars($item['volume_ml']) . '</td>
                    <td><span class="' . $statusClass . '">' . htmlspecialchars($item['status']) . '</span></td>
                    <td>' . htmlspecialchars($item['collected_by']) . '</td>
                    <td>' . htmlspecialchars($item['plasma_type'] ? $item['plasma_type'] : 'N/A') . '</td>
                    <td>
                        <button class="btn btn-warning btn-sm deliver-inventory" data-id="' . $item['id'] . '">Deliver</button>
                    </td>
                  </tr>';
        }
        
        echo '</tbody>
            </table>
        </div>
        </div>
        </div>
        </div>
        </div>';
    }
} else {
    echo '<div class="card">
            <div class="card-header">
                <h5 class="mb-0">Blood Collection Inventory</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle mr-2"></i> No inventory records available at the moment.
                </div>
                <p class="text-muted text-center">New blood collections will appear here once they are added to the system.</p>
            </div>
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
        plasmaType: document.getElementById('searchPlasmaType')
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
        plasmaType: document.getElementById('searchPlasmaType2')
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
            plasmaType: 9
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