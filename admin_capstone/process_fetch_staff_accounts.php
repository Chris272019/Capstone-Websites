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

// Handle individual column filter search
$searchFirstName = '';
$searchSurname = '';
$searchEmail = '';
$searchQuery = '';

if (isset($_POST['search_firstname']) && !empty($_POST['search_firstname'])) {
    $searchFirstName = mysqli_real_escape_string($conn, $_POST['search_firstname']);
    $searchQuery .= " AND firstname LIKE '%$searchFirstName%'";
}

if (isset($_POST['search_surname']) && !empty($_POST['search_surname'])) {
    $searchSurname = mysqli_real_escape_string($conn, $_POST['search_surname']);
    $searchQuery .= " AND surname LIKE '%$searchSurname%'";
}

if (isset($_POST['search_email']) && !empty($_POST['search_email'])) {
    $searchEmail = mysqli_real_escape_string($conn, $_POST['search_email']);
    $searchQuery .= " AND email_address LIKE '%$searchEmail%'";
}

// Fetch staff information from staff_account table with the filter applied
$sql = "SELECT firstname, surname, middlename, email_address AS email, role, id FROM staff_account WHERE 1" . $searchQuery;
$result = mysqli_query($conn, $sql);

// Debug query result
if (!$result) {
    echo "Error executing query: " . $conn->error;
    exit;
}

$staff_accounts = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $staff_accounts[] = $row;
    }
}

// Debug staff accounts
echo "<!-- Debug: Number of staff accounts found: " . count($staff_accounts) . " -->";

// Add custom CSS for the table
echo '<style>
    .staff-table-container {
        margin: 20px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .staff-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .staff-table th, .staff-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .staff-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    .staff-table tr:hover {
        background-color: #f5f5f5;
    }
    .search-input {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn-success {
        padding: 6px 12px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-success:hover {
        background-color: #218838;
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
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';

// Generate the HTML for displaying staff information
if (!empty($staff_accounts)) {
    echo '<div class="staff-table-container">
            <h3>Staff Accounts</h3>
            <div class="table-responsive">
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Surname</th>
                            <th>Middle Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="search_firstname" placeholder="Search First Name" class="search-input"></th>
                            <th><input type="text" id="search_surname" placeholder="Search Surname" class="search-input"></th>
                            <th><input type="text" id="search_middlename" placeholder="Search Middle Name" class="search-input"></th>
                            <th><input type="text" id="search_email" placeholder="Search Email" class="search-input"></th>
                            <th><input type="text" id="search_role" placeholder="Search Role" class="search-input"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($staff_accounts as $staff) {
                        echo '<tr>
                                <td>' . htmlspecialchars($staff['firstname']) . '</td>
                                <td>' . htmlspecialchars($staff['surname']) . '</td>
                                <td>' . htmlspecialchars($staff['middlename']) . '</td>
                                <td>' . htmlspecialchars($staff['email']) . '</td>
                                <td>' . htmlspecialchars($staff['role']) . '</td>
                                <td>
                                    <button class="btn btn-primary btn-sm edit-btn" data-id="' . $staff['id'] . '" data-firstname="' . htmlspecialchars($staff['firstname']) . '" data-surname="' . htmlspecialchars($staff['surname']) . '" data-middlename="' . htmlspecialchars($staff['middlename']) . '" data-email="' . htmlspecialchars($staff['email']) . '" data-role="' . htmlspecialchars($staff['role']) . '">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="' . $staff['id'] . '">
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
    echo '<div class="staff-table-container">
            <h3>Staff Accounts</h3>
            <p>No staff accounts available at the moment.</p>
            <p class="text-muted">New staff accounts will appear here once they are added to the system.</p>
          </div>';
}

// Add JavaScript for edit functionality
echo '<script>
$(document).ready(function() {
    // Check if SweetAlert2 is loaded
    if (typeof Swal === "undefined") {
        console.error("SweetAlert2 is not loaded!");
        return;
    }

    // Handle edit button click
    $(".edit-btn").click(function() {
        const staffId = $(this).data("id");
        const firstName = $(this).data("firstname");
        const surname = $(this).data("surname");
        const middleName = $(this).data("middlename");
        const email = $(this).data("email");
        const role = $(this).data("role");

        Swal.fire({
            title: "Edit Staff Account",
            html: `
                <form id="editStaffForm">
                    <input type="hidden" id="edit_staff_id" name="id" value="${staffId}">
                    <div class="form-group mb-3">
                        <label for="edit_firstname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_firstname" name="firstname" value="${firstName}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_surname" class="form-label">Surname</label>
                        <input type="text" class="form-control" id="edit_surname" name="surname" value="${surname}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_middlename" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="edit_middlename" name="middlename" value="${middleName}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="edit_email" name="email" value="${email}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="Interviewer" ${role === "Interviewer" ? "selected" : ""}>Interviewer</option>
                            <option value="Physician" ${role === "Physician" ? "selected" : ""}>Physician</option>
                            <option value="Phlebotomist" ${role === "Phlebotomist" ? "selected" : ""}>Phlebotomist</option>
                        </select>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: "Save Changes",
            cancelButtonText: "Cancel",
            focusConfirm: false,
            preConfirm: () => {
                const form = document.getElementById("editStaffForm");
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                // Validate form
                if (!data.firstname || !data.surname || !data.email || !data.role) {
                    Swal.showValidationMessage("Please fill in all required fields");
                    return false;
                }
                
                return data;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = result.value;
                
                $.ajax({
                    url: "process_update_staff.php",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Staff account has been updated successfully.",
                                    icon: "success",
                                    confirmButtonText: "OK"
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: data.error || "Failed to update staff account.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        } catch (e) {
                            console.error("Error parsing response:", e);
                            Swal.fire({
                                title: "Error!",
                                text: "Invalid server response.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", error);
                        Swal.fire({
                            title: "Error!",
                            text: "An error occurred while updating staff account.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
        });
    });
});
</script>';
?>

<script>
// Debug function to check if SweetAlert2 is loaded
function checkSweetAlert() {
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
        return false;
    }
    return true;
}

// Add event listeners for edit and delete buttons
$(document).ready(function() {
    console.log('Document ready');
    
    // Test SweetAlert
    if (checkSweetAlert()) {
        console.log('SweetAlert2 is loaded');
    }

    // Delete button functionality
    $('.delete-btn').on('click', function() {
        console.log('Delete button clicked');
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'process_delete_staff.php',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        console.log('Server response:', response);
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                Swal.fire('Deleted!', 'Staff account has been deleted.', 'success')
                                    .then(() => {
                                        location.reload();
                                    });
                            } else {
                                Swal.fire('Error!', data.error || 'Failed to delete staff account.', 'error');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            Swal.fire('Error!', 'Invalid server response.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        Swal.fire('Error!', 'An error occurred while deleting staff account.', 'error');
                    }
                });
            }
        });
    });
});
</script>
