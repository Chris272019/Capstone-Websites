<?php
// Include database connection
include('connection.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch hospital accounts with all fields
$sql = "SELECT 
            id, 
            hospital_name, 
            contact_person, 
            contact_number, 
            email_address,
            hospital_address,
            city,
            province,
            zip_code,
            registration_date
        FROM hospital_accounts 
        ORDER BY hospital_name ASC";
$result = $conn->query($sql);

// Debug query result
if (!$result) {
    echo "Error executing query: " . $conn->error;
    exit;
}

$hospital_accounts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hospital_accounts[] = $row;
    }
}

// Debug hospital accounts
echo "<!-- Debug: Number of hospital accounts found: " . count($hospital_accounts) . " -->";

// Add custom CSS for the table
echo '<style>
    .hospital-table-container {
        margin: 20px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .hospital-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .hospital-table th, .hospital-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .hospital-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    .hospital-table tr:hover {
        background-color: #f5f5f5;
    }
    .search-input {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn-danger {
        padding: 6px 12px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-danger:hover {
        background-color: #c82333;
    }
</style>';

// Add required CSS and JS libraries
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';

// Generate the HTML for displaying hospital accounts
if (!empty($hospital_accounts)) {
    echo '<div class="hospital-table-container">
            <h3>Hospital Accounts</h3>
            <div class="table-responsive">
                <table class="hospital-table">
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Email Address</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>Province</th>
                            <th>Zip Code</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="searchHospitalName" placeholder="Search Hospital Name" class="search-input"></th>
                            <th><input type="text" id="searchContactPerson" placeholder="Search Contact Person" class="search-input"></th>
                            <th><input type="text" id="searchContactNumber" placeholder="Search Contact Number" class="search-input"></th>
                            <th><input type="text" id="searchEmailAddress" placeholder="Search Email Address" class="search-input"></th>
                            <th><input type="text" id="searchAddress" placeholder="Search Address" class="search-input"></th>
                            <th><input type="text" id="searchCity" placeholder="Search City" class="search-input"></th>
                            <th><input type="text" id="searchProvince" placeholder="Search Province" class="search-input"></th>
                            <th><input type="text" id="searchZipCode" placeholder="Search Zip Code" class="search-input"></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($hospital_accounts as $hospital) {
                        echo '<tr>
                                <td>' . htmlspecialchars($hospital['hospital_name']) . '</td>
                                <td>' . htmlspecialchars($hospital['contact_person']) . '</td>
                                <td>' . htmlspecialchars($hospital['contact_number']) . '</td>
                                <td>' . htmlspecialchars($hospital['email_address']) . '</td>
                                <td>' . htmlspecialchars($hospital['hospital_address']) . '</td>
                                <td>' . htmlspecialchars($hospital['city']) . '</td>
                                <td>' . htmlspecialchars($hospital['province']) . '</td>
                                <td>' . htmlspecialchars($hospital['zip_code']) . '</td>
                                <td>' . htmlspecialchars($hospital['registration_date']) . '</td>
                                <td>
                                    <button class="btn-danger" onclick="deleteHospital(' . $hospital['id'] . ')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                              </tr>';
                    }
    echo '</tbody>
        </table>
    </div>
    </div>';
} else {
    echo '<div class="hospital-table-container">
            <h3>Hospital Accounts</h3>
            <p>No hospital accounts available at the moment.</p>
          </div>';
}
?>

<!-- Remove the Edit and Delete Modals since we're using SweetAlert2 now -->
<script>
    function editHospital(hospitalId) {
        // Show loading state
        Swal.fire({
            title: "Loading...",
            text: "Please wait while we fetch hospital details.",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            type: 'GET',
            url: 'fetch_hospital_details.php',
            data: { id: hospitalId },
            success: function(response) {
                Swal.fire({
                    title: "Edit Hospital Account",
                    html: response,
                    showCancelButton: true,
                    confirmButtonText: "Save Changes",
                    cancelButtonText: "Cancel",
                    focusConfirm: false,
                    preConfirm: () => {
                        const form = document.getElementById("editHospitalForm");
                        const formData = new FormData(form);
                        
                        // Validate form
                        if (!formData.get('hospital_name') || !formData.get('contact_number') || 
                            !formData.get('email_address') || !formData.get('hospital_address') || 
                            !formData.get('city') || !formData.get('province')) {
                            Swal.showValidationMessage("Please fill in all required fields");
                            return false;
                        }
                        
                        return formData;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = result.value;
                        
                        // Show loading state
                        Swal.fire({
                            title: "Processing...",
                            text: "Please wait while we update the hospital account.",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            type: 'POST',
                            url: 'process_update_hospital.php',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'Hospital account has been updated successfully.',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message || 'Failed to update hospital account.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'An unexpected error occurred. Please try again.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to fetch hospital details.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    function deleteHospital(hospitalId) {
        Swal.fire({
            title: "Delete Hospital Account",
            text: "Are you sure you want to delete this hospital account? This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while we delete the hospital account.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: 'process_delete_hospital.php',
                    data: { id: hospitalId },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Hospital account has been deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to delete hospital account.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }
</script>